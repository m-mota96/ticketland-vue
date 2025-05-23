<?php

namespace App\Http\Traits;
use App\Models\Access;
use App\Models\Payment;
use App\Models\Ticket;

trait OrderTrait {
    public static function registerPayment($event_id, $order, $order_id, $totalToPay, $type, $status, $discount, $reference) {
        $payment = Payment::create([
            'event_id'  => $event_id,
            'order_id'  => $order_id,
            'name'      => $order['name'],
            'email'     => $order['email'],
            'phone'     => $order['phone'],
            'type'      => $type,
            'reference' => $reference,
            'amount'    => $totalToPay,
            'code'      => $discount['code'],
            'discount'  => $discount['discountInt'],
            'status'    => $status
        ]);
        return [
            'success'    => true,
            'payment_id' => $payment->id
        ];
    }

    public static function registerAccess($payment_id, $tickets, $folios) {
        $insert = [];
        for ($i = 0; $i < sizeof($tickets); $i++) {
            $ticket = Ticket::select('id', 'name', 'valid')->find($tickets[$i]['id']);
            $insert[] = [
                'payment_id' => $payment_id,
                'ticket_id'  => $ticket->id,
                // 'code_id' => (isset($code->id)) ? $code->id : null,
                'folio'      => $folios[$i],
                'quantity'   => $ticket->valid,
                'name'       => $tickets[$i]['customer_name'],
                'email'      => $tickets[$i]['email'],
                'phone'      => $tickets[$i]['phone'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        Access::insert($insert);
        return ['success' => true];
    }
}