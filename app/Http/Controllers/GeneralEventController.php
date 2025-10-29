<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\ConektaPaymentTrait;
use App\Http\Traits\DigitalFemsaTrait;
use App\Http\Traits\ManageFilesTrait;
use App\Http\Traits\OrderTrait;
use App\Http\Traits\PaypalTrait;
use App\Http\Traits\SendMailTrait;
use App\Http\Traits\ValidateCodesTrait;
use App\Http\Traits\ValidateStockTrait;
use App\Models\Event;
use App\Models\Ticket;

class GeneralEventController extends Controller {

    public function event($url, $ticket = null) {
        $event = Event::with(['tickets' => function($query) {
            $query->select('*', DB::raw('quantity - (sales + reserved) available'), DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'))
            ->where('start_sale', '<=', date('Y-m-d'))
            ->where('stop_sale', '>=', date('Y-m-d'))
            ->whereRaw('quantity > (sales + reserved)');
        }, 'eventDates', 'profile', 'logo', 'location'])->whereRaw(DB::raw('BINARY url = "'.$url.'"'))->first();
        // dd($event->tickets);
        if (!$event) {
            return redirect('/');
        }
        if ($ticket) {
            $searchTicket = Ticket::where('event_id', $event->id)->where('name', $ticket)->first();
            if (!$searchTicket) {
                $ticket = null;
            }
        }
        return Inertia::render('Event/Event', [
            'event'  => $event,
            'ticket' => $ticket
        ]);
    }

    public function makePayment(Request $request) {
        try {
            $files = [];
            $event = Event::with(['profile', 'eventDates', 'location'])->where('status', 1)->find($request->order['event_id']);
            
            if (!$event) {
                return ResponseTrait::response('No es posible comprar boletos para el evento seleccionado.', ['type' => 'event'], true, 404);
            }

            if (!in_array($request->order['payment_method'], ['card', 'oxxo', 'paypal'])) {
                return ResponseTrait::response('Método de pago no reconocido.', ['type' => 'event'], true, 404);
            }

            $initialDateEvent = new \DateTime($event->eventDates[0]->date);
            $initialDateEvent->modify('-3 days');
            $initialDateEvent = $initialDateEvent->format('Y-m-d');
            if ((date('Y-m-d') >= $initialDateEvent) && $request->order['payment_method'] == 'oxxo') {
                return ResponseTrait::response('Ya no es posible realizar compras con Pago en Oxxo.', ['type' => 'event'], true, 409);
            }

            DB::beginTransaction();

            $discount = ['code_id' => null, 'code' => null, 'discount' => 0, 'discountInt' => 0];
            if ($request->order['code']) {
                $proccess = ValidateCodesTrait::validateCodes($request->order); // Valida si estan usando código de descuento y si éste es válido
                if (!$proccess['success']) {
                    DB::rollBack();
                    return ResponseTrait::response($proccess['msj'], ['type' => 'codes'], true, 409);
                }
                $discount = [
                    'code_id'     => $proccess['data']['code_id'],
                    'code'        => $request->order['code'],
                    'discount'    => $proccess['data']['discount'] / 100,
                    'discountInt' => $proccess['data']['discount']
                ];
            }

            $proccess = ValidateStockTrait::validateStock($request->tickets, $request->order['payment_method'], $discount); // Valida si hay disponibilidad de los boeltos elegidos
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response('', ['error' => $proccess['error'], 'type' => 'stock'], true, 409);
            }
            $totalToPay = intval($proccess['totalToPay']);

            $proccess = ManageFilesTrait::createPdf($request->informationTickets, $event, $discount['code']); // Crea los pdf de los boletos
            $files    = $proccess['files'];
            
            switch ($request->order['payment_method']) {
                case 'card':
                    $proccess = ConektaPaymentTrait::createCustomer($request->order); // Crea al cliente en Conekta
                    if (!$proccess['success']) {
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        DB::rollBack();
                        return ResponseTrait::response(
                            'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.',
                            ['type' => 'createCustomer', 'error' => $proccess['error']],
                            true,
                            409
                        );
                    }
                    // Se procesa el cobro a la tarjeta
                    $proccess = ConektaPaymentTrait::createOrder($event->name, $totalToPay, $proccess['customer_id'], $discount, $event->model_payment);
                    if (!$proccess['success']) {
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        DB::rollBack();
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $proccess   = OrderTrait::registerPayment($event->id, $request->order, $proccess['order_id'], $totalToPay, 'card', 'payed', $discount, $request->order['card']);
                    $payment_id = $proccess['payment_id'];
                    $proccess   = OrderTrait::registerAccess($proccess['payment_id'], $request->informationTickets, $files, $discount['code']);
                    SendMailTrait::index('tickets', $payment_id);
                    $txt = 'Pago realizado exitosamente.<br>Recibirás tus boletos en tu correo electrónico.<br> Si no los ves en tu bandeja de entrada, por favor revisa en Spam.';
                    break;
                case 'oxxo':
                    $proccess = DigitalFemsaTrait::createOrder($event->name, $totalToPay, $request->order, $discount, $event->model_payment); // Crea la referencia de pago en DigitalFemsa
                    if (!$proccess['success']) {
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        DB::rollBack();
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $dataReference               = $proccess;
                    $dataReference['name_event'] = $event->name;
                    $proccess                    = OrderTrait::registerPayment($event->id, $request->order, null, $totalToPay, 'oxxo', 'pending', $discount, $dataReference['reference']);
                    $payment_id                  = $proccess['payment_id'];
                    $proccess                    = OrderTrait::registerAccess($proccess['payment_id'], $request->informationTickets, $files, $discount['code']);
                    $proccess                    = ManageFilesTrait::createReference($event->id, $dataReference, $payment_id);
                    SendMailTrait::index('reference', $payment_id);
                    $txt = 'Registro realizado exitosamente.<br>Recibirás tu ficha de pago en tu correo electrónico.<br> Si no la ves en tu bandeja de entrada, por favor revisa en Spam.';
                    break;
                case 'paypal':
                    // Se procesa el pago con Paypal
                    $proccess = PaypalTrait::captureOrder($request->order['paypal_order_id']);
                    if (!$proccess['success']) {
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        DB::rollBack();
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $proccess   = OrderTrait::registerPayment($event->id, $request->order, $proccess['order_id'], $totalToPay, 'paypal', 'payed', $discount, 'N/A');
                    $payment_id = $proccess['payment_id'];
                    $proccess   = OrderTrait::registerAccess($proccess['payment_id'], $request->informationTickets, $files, $discount['code']);
                    SendMailTrait::index('tickets', $payment_id);
                    $txt = 'Pago realizado exitosamente.<br>Recibirás tus boletos en tu correo electrónico.<br> Si no los ves en tu bandeja de entrada, por favor revisa en Spam.';
                    break;
            }

            DB::commit();
            return ResponseTrait::response($txt);
        } catch (\Throwable $th) {
            if (sizeof($files) > 0) {
                ManageFilesTrait::deleteFiles($event->id, $files);
            }
            DB::rollBack();
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function verifyCodes(Request $request) {
        try {
            $proccess = ValidateCodesTrait::validateCodes($request->all(), false);
            if (!$proccess['success']) {
                return ResponseTrait::response($proccess['msj'], null, true, 409); // Hubo problemas con el código de descuento
            }
            return ResponseTrait::response('', $proccess['data']); // Código válido
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function createOrder(Request $request) {
        try {
            return PaypalTrait::createOrderPaypal($request->amount);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
