<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Mail;
use App\Models\Access;
use App\Models\Payment;
use App\Mail\SendReference;
use App\Mail\SendTickets;

trait SendMailTrait {
    public static function index($type, $payment_id) {
        switch ($type) {
            case 'tickets':
                return self::sendTickets($payment_id);
                break;
            case 'reference':
                return self::sendReference($payment_id);
                break;
        }
    }

    private static function sendTickets($payment_id) {
        $payment = Payment::with(['event.profile', 'accesses.ticket'])->find($payment_id);
        $payment->event->imgProfile = 'general/not_image.png';
        if ($payment->event->profile) {
            $payment->event->imgProfile = 'events/images/'.$payment->event->profile->name;
        }
        $infoTickets = [];
        foreach ($payment->accesses as $key => $a) {
            $infoTickets[$a->ticket->id]['ticket']   = $a->ticket->name;
            if ($a->promotion && !$payment->code) {
                $infoTickets[$a->ticket->id]['price']    = $a->ticket->price - round($a->ticket->price * ($a->promotion / 100));
            } else {
                $infoTickets[$a->ticket->id]['price']    = $a->ticket->price;
            }
            $infoTickets[$a->ticket->id]['quantity'] = isset($infoTickets[$a->ticket->id]['quantity']) ? $infoTickets[$a->ticket->id]['quantity'] + 1 : 1;
        }
        $infoTickets = array_values($infoTickets);
        $discount    = round($payment->amount * ($payment->discount / 100));
        $totals      = [
            'subtotal'                => $payment->amount,
            'discount'                => $payment->discount != 0 ? round($payment->amount * ($payment->discount / 100)) : 'N/A',
            'total'                   => $payment->amount - $discount,
            'commission'              => $payment->event->model_payment == 'separated' ? round(($payment->amount - $discount) * .12) : 0,
            'totalToPay'              => ($payment->amount - $discount) + round(($payment->amount - $discount) * .12),
        ];
        try {
            Mail::to($payment->email)->send(new SendTickets($payment, $infoTickets, $totals));
            return [
                'success' => true,
                'error'   => false
            ];
        } catch (\Throwable $th) {
            //throw $th;
            $logFile = fopen("logs/log_mail.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al enviar correo: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => true,
                'error'   => true,
                'msj'     => $th->getMessage()
            ];
        }
    }

    private static function sendReference($payment_id) {
        $payment = Payment::with(['event.profile', 'accesses.ticket'])->find($payment_id);
        $payment->event->imgProfile = 'general/not_image.png';
        if ($payment->event->profile) {
            $payment->event->imgProfile = 'events/images/'.$payment->event->profile->name;
        }
        try {
            Mail::to($payment->email)->send(new SendReference($payment));
            return [
                'success' => true,
                'error'   => false
            ];
        } catch (\Throwable $th) {
            //throw $th;
            $logFile = fopen("logs/log_mail.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al enviar correo: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => true,
                'error'   => true,
                'msj'     => $th->getMessage()
            ];
        }
    }
}
