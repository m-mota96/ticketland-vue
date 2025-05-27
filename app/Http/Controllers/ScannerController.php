<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Access;
use App\Models\Event;
use App\Models\EventDate;
use App\Models\Verified;

class ScannerController extends Controller {
    public function scanner($event_id) {
        $event = Event::find($event_id);
        return Inertia::render('Customer/Event/Scanner', [
            'event'   => $event
        ]);
    }

    public function validateAccess(Request $request) {
        try {
            $currentDate = date('Y-m-d');
            $currentDate = '2025-11-30';
            $dateEvent   = EventDate::where('date', $currentDate)->where('event_id', $request->event_id)->first();
            if (!$dateEvent) {
                return ResponseTrait::response('No es posible escanear los códigos fuera de las fechas del evento.', null, true, 409);
            }

            $folio       = Crypt::decrypt($request->access);
            // dd($folio);
            $access = Access::select('id', 'ticket_id', 'payment_id', 'name', 'email', 'phone', 'quantity', 'created_at')
            ->with(['ticket:id,event_id,name', 'payment:id,name,email,phone'])->where('folio', $folio)->whereHas('payment', function($query) {
                $payment = $query->where('status', 'payed')->orWhere('status', 'pay_free');
            })->first();
            if (!$access) {
                return ResponseTrait::response('No se encontró el código de acceso.', null, true, 409);
            }
            
            $verified = Verified::where('access_id', $access->id)->where('date', $currentDate)->first();
            if ($verified || $access->quantity == 0) {
                return ResponseTrait::response(
                    'Este código de acceso ya fue utilizado.',
                    [
                        'type'     => 'access_denied',
                        'verified' => $verified
                    ],
                    true, 409
                );
            }

            $access->quantity = $access->quantity - 1;
            $access->save();

            Verified::create([
                'event_id'  => $access->ticket->event_id,
                'access_id' => $access->id,
                'date'      => $currentDate,
                'time'      => date('H')
            ]);

            return ResponseTrait::response('', $access);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
