<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\ConektaPaymentTrait;
use App\Http\Traits\ManageFilesTrait;
use App\Http\Traits\OrderTrait;
use App\Http\Traits\ValidateCodesTrait;
use App\Http\Traits\ValidateStockTrait;
use App\Models\Event;

class GeneralEventController extends Controller {
    public function event($url) {
        $event = Event::with(['tickets' => function($query) {
            $query->select('*', DB::raw('quantity - (sales + reserved) available'))
            ->where('start_sale', '<=', date('Y-m-d'))
            ->where('stop_sale', '>=', date('Y-m-d'))
            ->whereRaw('quantity > (sales + reserved)');
        }, 'eventDates', 'profile', 'logo', 'location'])->where(DB::raw('BINARY url'), $url)->first();
        if (!$event) {
            return redirect('/');
        }
        return Inertia::render('Event/Event', [
            'event'   => $event
        ]);
    }

    public function makePayment(Request $request) {
        try {
            $files    = [];
            $event_id = $request->order['event_id'];
            $event    = Event::with(['profile', 'eventDates', 'location'])->find($event_id);
            
            if (!$event) {
                return ResponseTrait::response('No se encuentra el evento solicitado.', ['type' => 'event'], true, 404);
            }
            DB::beginTransaction();

            $proccess = ValidateStockTrait::validateStock($request->tickets, $request->order['payment_method']); // Valida si aún hay disponibilidad de los boeltos elegidos
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response('', ['error' => $proccess['error'], 'type' => 'stock'], true, 409);
            }
            $totalToPay = $proccess['totalToPay'];

            $discount = ['code' => null, 'discount' => 0];
            if ($request->order['code']) {
                $proccess = ValidateCodesTrait::validateCodes($request->order); // Valida si estan usando código de descuento y si éste es válido
                if (!$proccess['success']) {
                    DB::rollBack();
                    return ResponseTrait::response($proccess['msj'], ['type' => 'codes'], true, 409);
                }
                $discount = [
                    'code_id'  => $proccess['data']['code_id'],
                    'code'     => $request->order['code'],
                    'discount' => $proccess['data']['discount'] / 100
                ];
            }

            $proccess = ManageFilesTrait::createPdf($request->informationTickets, $event); // Crea los pdf de los boletos
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
                    $proccess = ConektaPaymentTrait::createOrder($event->name, $totalToPay, $proccess['customer_id'], $discount);
                    if (!$proccess['success']) {
                        ManageFilesTrait::deleteFiles($event->id, $files);
                        DB::rollBack();
                        return ResponseTrait::response($proccess['msj'], ['type' => 'payment'], true, 409);
                    }
                    $proccess = OrderTrait::registerPayment($event->id, $request->order, $proccess['order_id'], $totalToPay, 'card', 'payed', $discount['code_id'], $request->order['card']);
                    $proccess = OrderTrait::registerAccess($proccess['payment_id'], $request->informationTickets, $files);
                    break;
                case 'cash':
                    # code...
                    break;
            }

            DB::commit();
            return ResponseTrait::response('Los boletos se enviarán a su correo electrónico');
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
}
