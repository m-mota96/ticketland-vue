<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;

trait ValidateStockTrait {
    public static function validateStock($tickets, $payment_method, $discount) { // Verifica si todavía quedan boletos disponibles
        $success    = true;
        $errors     = [];
        $totalToPay = 0;
        // dd($tickets, $discount);
        foreach ($tickets as $key => $t) {
            $ticket = Ticket::select('id', 'name', DB::raw('quantity - (sales + reserved) AS available'), 'sales', 'reserved', 'price', DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'))
            ->where('id', $t['id'])
            ->where('status', 1)
            ->where('start_sale', '<=', date('Y-m-d'))
            ->where('stop_sale', '>=', date('Y-m-d'))
            ->first();
            if (!$ticket) { // Se esta intentando comprar el boleto fuera del rango de fechas establecidas o que ya no esta activo
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
                        case 'paypal':
                            $ticket->sales = $ticket->sales + $t['quantity'];
                            break;
                        case 'oxxo':
                            $ticket->reserved = $ticket->reserved + $t['quantity'];
                            break;
                    }
                    $ticket->save();
                    $priceDiscount = $ticket->promotion
                    ? ($ticket->price - round($ticket->price * ($ticket->promotion / 100))) // Si hay una promoción activa se calcula el precio con descuento.
                    : 0; // Si no la hay lo dejamos en 0.
                    $price = $ticket->promotion && !$t['code_id']
                    ? $priceDiscount // Si hay una promoción activa y NO ingresaron cupón de descuento se toma el precio con descuento.
                    : (!$t['code_id']
                        ? $ticket->price // Si no hay promoción activa y NO hay cupón de descuento se toma el precio normal del boleto.
                        : ($ticket->price - round($ticket->price * ($t['code_discount'] / 100)))); // Si no hay promoción activa y SI hay un cupón, se calcula el precio menos el descuento del cupón.
                    $totalToPay = $totalToPay + ($price * $t['quantity']);
                }
            }
        }
        return [
            'success'    => $success,
            'error'      => $errors,
            'totalToPay' => intval($totalToPay)
        ];
    }
}