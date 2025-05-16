<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\CreateFilesTrait;
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
            $event_id = $request->order['event_id'];
            $event    = Event::with(['profile', 'eventDates', 'location'])->find($event_id);
            
            if (!$event) {
                return ResponseTrait::response('No se encuentra el evento solicitado.', ['type' => 'event'], true, 404);
            }
            DB::beginTransaction();

            $proccess = ValidateStockTrait::validateStock($request->tickets, $request->order['payment_method']);
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response('', ['error' => $proccess['error'], 'type' => 'stock'], true, 409);
            }

            $discount = 0;
            if ($request->order['code']) {
                $proccess = ValidateCodesTrait::validateCodes($request->order);
                if (!$proccess['success']) {
                    DB::rollBack();
                    return ResponseTrait::response($proccess['msj'], ['type' => 'codes'], true, 409);
                }
                $discount = $proccess['data']['discount'] / 100;
            }

            $proccess = CreateFilesTrait::createPdf($request->informationTickets, $event);

            dd($proccess['files']);

            // DB::commit();
        } catch (\Throwable $th) {
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
