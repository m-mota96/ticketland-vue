<?php

namespace App\Http\Traits;
use App\Models\Code;

trait ValidateCodesTrait {
    public static function validateCodes($tickets, $save = true) { // Verifica si estan usando códigos de descuento
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
                    if (!$code) { // No existe el código ingresado
                        $errors[] = 'El código <b>'.$t['code'].'</b> no existe.';
                        $success  = false;
                    } else {
                        if (sizeof($code->tickets) === 0) { // El código no aplica para el boleto
                            $errors[] = 'El código <b>'.$t['code'].'</b> no aplica para el boleto <b>'.$t['name'].'</b>.';
                            $success  = false;
                        }
                        if ($code->expiration) {
                            if ($code->expiration < date('Y-m-d')) {
                                $errors[] = 'El código <b>'.$t['code'].'</b> ha expirado.';
                                $success  = false;
                            }
                        }
                        $count                  = isset($groupCodes[$code->tickets[0]->name]) ? $count + $groupCodes[$code->tickets[0]->name]['quantity'] : 1;
                        // $groupCodes[$t['code']] = [ // Vemos cuántos y que código estan usando 
                        $groupCodes[$code->tickets[0]->name] = [ // Vemos cuántos y que código estan usando 
                            'code'      => $t['code'],
                            'ticket_id' => sizeof($code->tickets) > 0 ? $code->tickets[0]->id : 0,
                            'price'     => sizeof($code->tickets) > 0 ? $code->tickets[0]->price : 0,
                            'discount'  => $code->discount,
                            'quantity'  => $count
                        ];
                    }
                }
            }
            // if ($success) {
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
            // }
            // dd($groupCodes);
            return [
                'success' => $success,
                'error'   => $errors,
                'code'    => 409,
                'msj'     => null,
                'data'    => $groupCodes
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