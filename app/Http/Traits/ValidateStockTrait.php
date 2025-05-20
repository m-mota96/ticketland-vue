<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;

trait ValidateStockTrait {
    public static function validateStock($tickets, $payment_method) { // Verifica si todavía quedan boletos disponibles
        $success    = true;
        $errors     = [];
        $totalToPay = 0;
        foreach ($tickets as $key => $t) {
            $ticket = Ticket::select('id', 'name', DB::raw('quantity - (sales + reserved) AS available'), 'sales', 'price')
            ->where('id', $t['id'])
            ->where('start_sale', '<=', date('Y-m-d'))
            ->where('stop_sale', '>=', date('Y-m-d'))
            ->first();
            if (!$ticket) { // Se esta intentando comprar el boleto fuera del rango de fechas establecidas
                $errors[] = 'El boleto <b>'.$t['name'].'</b> ya no esta disponible.';
                $success  = false;
            } else {
                if ($ticket->available == 0 || $ticket->available < $t['quantity']) { // Ya se vendieron todos los boletos o no se ajusta la cantidad que desean comprar
                    $errors[] = $ticket->available == 0 ? 
                    'Ya no hay disponibilidad del boleto <b>'.$ticket->name.'</b>.' : 
                    'Solo quedan <b>'.$ticket->available.'</b> boletos disponibles de <b>'.$ticket->name.'</b>.';
                    $success  = false;
                } else { // Correcto
                    switch ($payment_method) {
                        case 'card':
                            $ticket->sales = $ticket->sales + $t['quantity'];
                            break;
                        case 'cash':
                            $ticket->reserved = $ticket->reserved + $t['quantity'];
                            break;
                    }
                    $ticket->save();
                    $totalToPay = $totalToPay + ($t['quantity'] * $ticket->price);
                }
            }
        }
        return [
            'success'    => $success,
            'error'      => $errors,
            'totalToPay' => $totalToPay
        ];
    }
}