<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;
use App\Models\Access;
use App\Models\Code;
use App\Models\Payment;
use App\Models\Response;
use App\Models\Ticket;

trait OrderTrait {
    public static function registerPayment($event_id, $order, $order_id, $totalToPay, $status, $discount, $reference) {
        try {
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
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_registerPayment.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error registrando pago: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success'    => false,
                'payment_id' => null
            ];
        }
    }

    public static function registerAccess($payment_id, $tickets, $folios, $foliosEncrypted) {
        // for ($i = 0; $i < sizeof($tickets); $i++) {
        //     $ticket = Ticket::select('id', 'name', 'price', DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'), 'valid')->find($tickets[$i]['id']);
        //     $access = Access::create([
        //         'payment_id'    => $payment_id,
        //         'ticket_id'     => $ticket->id,
        //         'code_id'       => !empty($tickets[$i]['code_id']) ? $tickets[$i]['code_id'] : null,
        //         'folio'         => $folios[$i],
        //         'quantity'      => $ticket->valid,
        //         'name'          => $tickets[$i]['customer_name'],
        //         'email'         => $tickets[$i]['email'],
        //         'phone'         => $tickets[$i]['phone'],
        //         'code_name'     => !empty($tickets[$i]['code_id']) ? $tickets[$i]['code'] : null,
        //         'code_discount' => !empty($tickets[$i]['code_id']) ? $tickets[$i]['code_discount'] : null,
        //         'price'         => $ticket->price,
        //         'promotion'     => empty($tickets[$i]['code_id']) ? $ticket->promotion : null
        //     ]);
        // }
        try {
            $pos = 0;
            foreach ($tickets as $key => $t) {
                $ticket = Ticket::select('id', 'name', 'price', DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'), 'valid', 'package')->find($t['id']);
                $prefix = $ticket->package === 1 ? 'P-' : 'T-';
                $uniqid = uniqid($prefix);
                foreach ($t['inputs'] as $key2 => $input) {
                    $access = Access::create([
                        'payment_id'      => $payment_id,
                        'ticket_id'       => $ticket->id,
                        'code_id'         => !empty($t['code_id']) ? $t['code_id'] : null,
                        'unification'     => $uniqid,
                        'folio'           => $folios[$pos],
                        'folio_encrypted' => $foliosEncrypted[$pos],
                        'quantity'        => $ticket->valid,
                        'name'            => $input['name'],
                        'email'           => $input['email'],
                        'phone'           => $input['phone'],
                        'code_name'       => !empty($t['code_id']) ? $t['code'] : null,
                        'code_discount'   => !empty($t['code_id']) ? $t['code_discount'] : null,
                        'price'           => $ticket->price,
                        'promotion'       => empty($t['code_id']) ? $ticket->promotion : null
                    ]);
                    for ($i = 0; $i < sizeof($input['question']); $i++) { 
                        if ($input['question'][$i]['id'] !== null && !empty($input['question'][$i]['response'])) {
                            Response::create([
                                'question_id' => $input['question'][$i]['id'],
                                'access_id'   => $access->id,
                                'response'    => $input['question'][$i]['response']
                            ]);
                        }
                    }
                    $pos++;
                }
            }
            return ['success' => true];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_registerAccess.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error registrando accesos: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false];
        }
    }

    public static function storeCodes($payment_method, $discount) {
        try {
            if ($discount['code_id']) {
                $code         = Code::find($discount['code_id']);
                $code->stored = $code->stored - 1;
                switch ($payment_method) {
                    case 'card':
                    case 'paypal':
                        $code->used = $code->used + 1;
                        break;
                    case 'oxxo':
                        $code->reserved = $code->reserved + 1;
                        break;
                }
                $code->save();
            }

            return ['success' => true];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_storeCodes.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error general: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false];
        }
    }

    public static function storeTickets($payment_method, $tickets) {
        try {
            foreach ($tickets as $key => $t) {
                $ticket         = Ticket::find($t['id']);
                $ticket->stored = $ticket->stored - $t['quantity_to_purchase'];
                switch ($payment_method) {
                    case 'card':
                    case 'paypal':
                        $ticket->sales = $ticket->sales + $t['quantity_to_purchase'];
                        break;
                    case 'oxxo':
                        $ticket->reserved = $ticket->reserved + $t['quantity_to_purchase'];
                        break;
                }
                $ticket->save();
            }

            return ['success' => true];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_storeTickets.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error general: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false];
        }
    }

    public static function stagedCodes($discount) {
        try {
            if ($discount['code_id']) {
                $code         = Code::find($discount['code_id']);
                $code->stored = $code->stored - 1;
                $code->save();
            }

            return ['success' => true];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_stagedCodes.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error general: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false];
        }
    }

    public static function stagedTickets($tickets) {
        try {
            foreach ($tickets as $key => $t) {
                $ticket         = Ticket::find($t['id']);
                $ticket->stored = $ticket->stored - $t['quantity_to_purchase'];
                $ticket->save();
            }

            return ['success' => true];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_stagedTickets.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error general: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false];
        }
    }
}