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
        $event = Event::with([
            'tickets' => function($query) {
                $query->select('*', DB::raw('quantity - (sales + reserved) available'), DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'))
                ->where('start_sale', '<=', date('Y-m-d'))
                ->where('stop_sale', '>=', date('Y-m-d'))
                ->whereRaw('quantity > (sales + reserved)');
            },
            'paymentMethods' => function($query) {
                $query->where('active', true);
            },
            'eventDates', 'profile', 'logo', 'location'
        ])->whereRaw(DB::raw('BINARY url = "'.$url.'"'))->first();
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
            $event = Event::with(['profile', 'eventDates', 'location', 'paymentMethods' => function($query) {
                $query->where('active', true);
            }])->where('status', 1)->find($request->order['event_id']);
            
            if (!$event) {
                return ResponseTrait::response('No es posible comprar boletos para el evento seleccionado.', ['type' => 'event'], true, 404);
            }
            
            $paymentMethods = $event->paymentMethods->pluck('sku')->toArray();
            if (!in_array($request->order['payment_method'], $paymentMethods)) {
                return ResponseTrait::response('Método de pago no reconocido.', ['type' => 'event'], true, 404);
            }

            $commissionTicketland = 0;
            foreach ($event->paymentMethods as $key => $pm) {
                if ($pm->sku === $request->order['payment_method']) {
                    $commissionTicketland = floatval($pm->pivot->commission);
                    break;
                }
            }

            // $initialDateEvent = new \DateTime($event->eventDates[0]->date);
            // $initialDateEvent->modify('-3 days');
            // $initialDateEvent = $initialDateEvent->format('Y-m-d');
            // if ((date('Y-m-d') >= $initialDateEvent) && $request->order['payment_method'] == 'oxxo') {
            //     return ResponseTrait::response('Ya no es posible realizar compras con Pago en Oxxo.', ['type' => 'event'], true, 409);
            // }

            DB::beginTransaction();

            $discount = ['code_id' => null, 'code' => null, 'discount' => 0, 'discountInt' => 0, 'tickets' => []];
            if ($request->order['code']) {
                $proccess = ValidateCodesTrait::validateCodes($request->order, true, $request->tickets); // Valida si estan usando código de descuento y si éste es válido
                if (!$proccess['success']) {
                    DB::rollBack();
                    return ResponseTrait::response($proccess['msj'], ['type' => 'codes'], true, 409);
                }
                $discount = [
                    'code_id'     => $proccess['data']['code_id'],
                    'code'        => $request->order['code'],
                    'discount'    => $proccess['data']['discount'] / 100,
                    'discountInt' => $proccess['data']['discount'],
                    'tickets'     => $proccess['data']['tickets']
                ];
            }

            $proccess = ValidateStockTrait::validateStock($request->tickets, $request->order['payment_method'], $discount); // Valida si hay disponibilidad de los boeltos elegidos
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response('', ['error' => $proccess['error'], 'type' => 'stock'], true, 409);
            }
            $subtotal   = intval($proccess['totalToPay']);
            $commission = $event->model_payment === 'separated' ? round($subtotal * $commissionTicketland) : 0;
            $total      = intval($subtotal + $commission);

            $proccess = ManageFilesTrait::createPdf($request->informationTickets, $event, $discount['code']); // Crea los pdf de los boletos
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response($proccess['msj'], ['type' => 'general'], true, 409);
            }
            $files = $proccess['files'];
            
            switch ($request->order['payment_method']) {
                case 'card':
                    // Se procesa el cobro a la tarjeta
                    $proccess = ConektaPaymentTrait::createOrder($event->name, $total, $request->order);
                    if (!$proccess['success']) {
                        DB::rollBack();
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $orderClientId = $proccess['order_id']; // Id de la transacción en Conekta
                    $typeSend      = 'tickets'; // Indicador para que mande los boletos al cliente
                    $statusPayment = 'payed';
                    $reference     = $request->order['card'];
                    $txt = 'Pago realizado exitosamente.<br>Recibirás tus boletos en tu correo electrónico.<br> Si no los ves en tu bandeja de entrada, por favor revisa en Spam.';
                    break;
                case 'oxxo':
                    $proccess = DigitalFemsaTrait::createOrder($event->name, $total, $request->order); // Crea la referencia de pago en DigitalFemsa
                    if (!$proccess['success']) {
                        DB::rollBack();
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $orderClientId = $proccess['order_id']; // Id de la transacción en DigitalFemsa
                    $typeSend      = 'reference';  // Indicador para que mande la referencia de pago al cliente
                    $statusPayment = 'pending';
                    $reference     = $proccess['reference'];
                    $dataReference = $proccess;
                    $proccess      = ManageFilesTrait::createReference($event->id, $dataReference, $orderClientId); // Crea el pdf de la referencia de pago
                    if (!$proccess['success']) {
                        DB::rollBack();
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        return ResponseTrait::response($proccess['msj'], ['type' => 'general'], true, 409);
                    }
                    $txt = 'Registro realizado exitosamente.<br>Recibirás tu ficha de pago en tu correo electrónico.<br> Si no la ves en tu bandeja de entrada, por favor revisa en Spam.';
                    break;
                case 'paypal':
                    // Se procesa el pago con Paypal
                    $proccess = PaypalTrait::captureOrder($request->order['paypal_order_id']);
                    if (!$proccess['success']) {
                        DB::rollBack();
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $orderClientId = $proccess['order_id']; // Id de la transacción en PayPal
                    $typeSend      = 'tickets'; // Indicador para que mande los boletos al cliente
                    $statusPayment = 'payed';
                    $reference     = 'N/A';
                    $txt = 'Pago realizado exitosamente.<br>Recibirás tus boletos en tu correo electrónico.<br> Si no los ves en tu bandeja de entrada, por favor revisa en Spam.';
                    break;
            }
            // Se registra la información de pago en la DB
            $proccess   = OrderTrait::registerPayment($event->id, $request->order, $orderClientId, $subtotal, $statusPayment, $discount, $reference);
            $payment_id = $proccess['payment_id'];
            // Se registran los accesos en la DB
            $proccess   = OrderTrait::registerAccess($proccess['payment_id'], $request->informationTickets, $files);
            // Se envían los boletos o la referencia de pago según sea el caso
            SendMailTrait::index($typeSend, $payment_id);

            DB::commit();
            return ResponseTrait::response($txt);
        } catch (\Throwable $th) {
            DB::rollBack();
            $logFile = fopen("logs/log_general.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error general: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            if (sizeof($files) > 0) {
                ManageFilesTrait::deleteFiles($event->id, $files);
            }
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta al organizador del evento.<br>'.$th->getMessage(), ['type' => 'general'], true, 409);
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
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function createOrder(Request $request) {
        try {
            return PaypalTrait::createOrderPaypal($request->amount);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
