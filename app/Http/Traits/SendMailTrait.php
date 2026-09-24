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
        $payment = Payment::with(['event.profile', 'accesses.ticket', 'event.paymentMethods'])
        ->addSelect([
            'total_order' => Access::query()
                ->from('accesses')
                ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
                ->whereColumn('accesses.payment_id', 'payments.id')
                ->selectRaw('
                    SUM(
                        CASE
                            WHEN tickets.package = 1
                            THEN accesses.price / (
                                SELECT COUNT(*)
                                FROM accesses a2
                                WHERE a2.ticket_id = accesses.ticket_id
                                AND a2.payment_id = accesses.payment_id
                            )
                            ELSE accesses.price
                        END
                    )
                ')
        ])
        ->find($payment_id);
        $payment->event->imgProfile = 'general/slide_ticketland.png';
        if ($payment->event->profile) {
            $payment->event->imgProfile = 'events/images/'.$payment->event->profile->name;
        }

        $infoTickets = [];
        foreach ($payment->accesses as $key => $a) {
            $price = $a->price;
            if ($a->code_discount) {
                $price = $a->price - round($a->price * ($a->code_discount / 100));
            } else if ($a->promotion) {
                $price = $a->price - round($a->price * ($a->promotion / 100));
            }
            $infoTickets[$a->ticket_id]['ticket']   = $a->ticket->name;
            $infoTickets[$a->ticket_id]['price']    = $price;
            $infoTickets[$a->ticket_id]['quantity'] = isset($infoTickets[$a->ticket_id]['quantity'])
                ? ($a->ticket->package === 0
                    ? $infoTickets[$a->ticket_id]['quantity'] + 1
                    : $infoTickets[$a->ticket_id]['quantity']
                )
                : 1;
        }
        $infoTickets = array_values($infoTickets);

        $commissionTicketland = 0;
        foreach ($payment->event->paymentMethods as $key => $pm) {
            if ($pm->sku === $payment->type) {
                $commissionTicketland = floatval($pm->pivot->commission);
                break;
            }
        }

        $commission = $payment->event->model_payment === 'separated' ? round($payment->amount * $commissionTicketland) : 0;
        $totals     = [
            'subtotal'   => $payment->amount,
            'commission' => $commission,
            'total'      => $payment->amount + $commission,
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
