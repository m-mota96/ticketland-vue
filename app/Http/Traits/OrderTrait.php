<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;
use App\Models\Access;
use App\Models\Payment;
use App\Models\Ticket;

trait OrderTrait {
    public static function registerPayment($event_id, $order, $order_id, $totalToPay, $status, $discount, $reference) {
        $payment = Payment::create([
            'event_id'          => $event_id,
            'payment_method_id' => $order['payment_method_id'],
            'order_id'          => $order_id,
            'name'              => $order['name'],
            'email'             => $order['email'],
            'phone'             => $order['phone'],
            'type'              => $order['payment_method'],
            'reference'         => $reference,
            'amount'            => $totalToPay,
            'code'              => $discount['code'],
            'discount'          => $discount['discountInt'],
            'status'            => $status
        ]);
        return [
            'success'    => true,
            'payment_id' => $payment->id
        ];
    }

    public static function registerAccess($payment_id, $tickets, $folios) {
        for ($i = 0; $i < sizeof($tickets); $i++) {
            $ticket = Ticket::select('id', 'name', 'price', DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'), 'valid')->find($tickets[$i]['id']);
            $access = Access::create([
                'payment_id'    => $payment_id,
                'ticket_id'     => $ticket->id,
                'code_id'       => $tickets[$i]['code_id'] ? $tickets[$i]['code_id'] : null,
                'folio'         => $folios[$i],
                'quantity'      => $ticket->valid,
                'name'          => $tickets[$i]['customer_name'],
                'email'         => $tickets[$i]['email'],
                'phone'         => $tickets[$i]['phone'],
                'code_name'     => $tickets[$i]['code_id'] ? $tickets[$i]['code'] : null,
                'code_discount' => $tickets[$i]['code_id'] ? $tickets[$i]['code_discount'] : null,
                'price'         => $ticket->price,
                'promotion'     => !$tickets[$i]['code_id'] ? $ticket->promotion : null
            ]);
        }
        return ['success' => true];
    }
}