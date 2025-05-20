<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Mail;
use App\Models\Access;
use App\Models\Payment;
use App\Mail\SendTickets;

trait SendMailTrait {
    public static function index($type, $payment_id, $email = null) {
        switch ($type) {
            case 'tickets':
                self::sendTickets($payment_id, $email);
                break;
            case 'reference':
                # code...
                break;
        }
    }

    private static function sendTickets($payment_id, $email) {
        $payment = Payment::with(['event.profile', 'accesses.ticket'])->find($payment_id);
        if ($email) {
            $payment->email = $email;
            $payment->save();
        }
        $payment->event->imgProfile = 'general/not_image.png';
        if ($payment->event->profile) {
            $payment->event->imgProfile = 'events/images/'.$payment->event->profile->name;
        }
        $infoTickets = [];
        foreach ($payment->accesses as $key => $a) {
            $infoTickets[$a->ticket->id]['ticket']   = $a->ticket->name;
            $infoTickets[$a->ticket->id]['price']    = $a->ticket->price;
            $infoTickets[$a->ticket->id]['quantity'] = isset($infoTickets[$a->ticket->id]['quantity']) ? $infoTickets[$a->ticket->id]['quantity'] + 1 : 1;
        }
        $infoTickets = array_values($infoTickets);
        Mail::to($payment->email)->send(new SendTickets($payment, $payment->event, $infoTickets, $payment->accesses));
        // dd($infoTickets);
    }
}
