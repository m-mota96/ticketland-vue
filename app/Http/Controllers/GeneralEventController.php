<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Code;
use App\Models\Event;
use App\Models\Ticket;

class GeneralEventController extends Controller {
    protected $currentDate, $currentDateTime;
    public function __construct() {
        $this->currentDate     = date('Y-m-d');
        $this->currentDateTime = date('Y-m-d H:i:s');
    }

    public function event($url) {
        $event = Event::with(['tickets' => function($query) {
            $query->where('start_sale', '<=', $this->currentDate)->where('stop_sale', '>=', $this->currentDate);
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
            // dd($request->all());
            DB::beginTransaction();

            $proccess = $this->validateStock($request->tickets);
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response($proccess['msj'], ['type' => 'stock', 'error' => $proccess['error'], 'code' => $proccess['code']], true, $proccess['code']);
            }

            $proccess = $this->codesAndFiles($request->informationTickets);
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response($proccess['msj'], ['type' => 'stock', 'error' => $proccess['error'], 'code' => $proccess['code']], true, $proccess['code']);
            }

            // DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    private function validateStock($tickets) { // Verifica si todavía quedan boletos disponibles
        try {
            $success = true;
            $errors  = [];
            foreach ($tickets as $key => $t) {
                $ticket = Ticket::select('id', 'name', DB::raw('quantity - sales AS available'), 'sales')
                ->where('id', $t['id'])
                ->where('start_sale', '<=', $this->currentDate)
                ->where('stop_sale', '>=', $this->currentDate)
                ->first();
                if (!$ticket) { // Se esta intentando comprar el boleto fuera del rango de fechas establecidas
                    $errors[] = 'El boleto <b>'.$t['name'].'</b> ya no esta disponible.';
                    $success  = false;
                } else {
                    if ($ticket->available == 0 || $ticket->available < $t['quantity']) { // Ya se vendieron todo los boletos o no se ajusta la cantidad que desean comprar
                        $errors[] = $ticket->available == 0 ? 
                        'Ya no hay disponibilidad del boleto <b>'.$ticket->name.'</b>.' : 
                        'Solo quedan <b>'.$ticket->available.'</b> boletos disponibles de <b>'.$ticket->name.'</b>.';
                        $success  = false;
                    } else { // Correcto
                        $ticket->sales = $ticket->sales + $t['quantity'];
                        $ticket->save();
                    }
                }
            }
            return [
                'success' => $success,
                'error'   => $errors,
                'code'    => 409,
                'msj'     => null
            ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'error'   => 'Ocurrio un error '.$th->getMessage(),
                'code'    => 500,
                'msj'     => 'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.'
            ];
        }
    }

    private function codesAndFiles($tickets) { // Verificamos si estan usando códigos de descuento y creamos los pdf de los boletos
        try {
            $success    = true;
            $errors     = [];
            $groupCodes = [];
            $count      = 0;
            foreach ($tickets as $key => $t) {
                if ($t['code']) { // Verificamos si puso código de descuento
                    $code = Code::with(['tickets' => function($query) use($t) {
                        $query->where('ticket_id', $t['id']);
                    }])->where('code', $t['code'])->first();
                    // dd($code);
                    if (!$code) { // No existe el código ingresado
                        $errors[] = 'El código <b>'.$t['code'].'</b> no existe';
                        $success  = false;
                    } else {
                        if (sizeof($code->tickets) === 0) { // El código no aplica para el boleto
                            $errors[] = 'El código <b>'.$t['code'].'</b> no aplica para el boleto <b>'.$t['name'].'</b>.';
                            $success  = false;
                        }
                        if ($code->expiration) {
                            if ($code->expiration < $this->currentDate) {
                                $errors[] = 'El código <b>'.$t['code'].'</b> ha expirado.';
                                $success  = false;
                            }
                        }
                        $count                  = isset($groupCodes[$t['code']]) ? $count + $groupCodes[$t['code']]['quantity'] : 1;
                        $groupCodes[$t['code']] = ['code' => $t['code'], 'quantity' => $count]; // Vemos cuántos y que código estan usando 
                    }
                }
            }
            if ($success) {
                foreach ($groupCodes as $key => $gc) {
                    $used = 0;
                    // print_r($gc['quantity'].' ');
                    $code = Code::with(['tickets'])->where('code', $gc['code'])->first();
                    foreach ($code->tickets as $key2 => $t) {
                        $used = $used + $t->pivot->used + $t->pivot->reserved;
                    }
                    if ($gc['quantity'] > ($code->quantity - $used)) {
                        $success  = false;
                        $errors[] = ($code->quantity - $used) === 0 ?
                        'Ya no hay disponibilidad del código <b>'.$code->code.'</b>.' : 
                        'Solo quedan <b>'.($code->quantity - $used).'</b> códigos disponibles de <b>'.$code->code.'</b>.';
                    }
                }
            }
            // dd($groupCodes);
            return [
                'success' => $success,
                'error'   => $errors,
                'code'    => 409,
                'msj'     => null
            ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'error'   => 'Ocurrio un error '.$th->getMessage(),
                'code'    => 500,
                'msj'     => 'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.'
            ];
        }
    }
}
