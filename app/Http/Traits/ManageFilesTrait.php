<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\DateFormatTrait;
use App\Models\Ticket;
use Carbon\Carbon;

trait ManageFilesTrait {
    public static function createPdf($tickets, $event, $discount) {
        try {
            if (!file_exists('events/pdf/'.$event->id)) {
                mkdir('events/pdf/'.$event->id, 0777, true);
            }

            $files = [];
            $pos   = 0;
            foreach ($tickets as $key => $t) {
                $ticket    = Ticket::select('id', 'name', 'price', DB::raw('IF(CURDATE() > date_promotion, NULL, promotion) promotion'))->find($t['id']);
                $price     = $ticket->price;
                $promotion = null;
                if ($ticket->promotion && !$discount['code']) {
                    $price     = $ticket->price - round($ticket->price * ($ticket->promotion / 100));
                    $promotion = $ticket->promotion;
                }
                if (!$ticket->promotion && $discount['code']) {
                    $price     = $ticket->price - round($ticket->price * ($discount['discount']));
                    $promotion = $discount['discountInt'];
                }
                if ($ticket->promotion && $discount['code']) {
                    $price     = $ticket->price - round($ticket->price * ($discount['discount']));
                    $promotion = $discount['discountInt'];
                }
                foreach ($t['inputs'] as $key2 => $input) {
                    $data[$pos]['name']         = $ticket->name;
                    $data[$pos]['eventName']    = $event->name;
                    $data[$pos]['eventDesc']    = $event->description;
                    $data[$pos]['eventAddress'] = $event->location ? $event->location->address : 'Sin información';
                    $data[$pos]['eventProfile'] = $event->profile ? asset('events/images/'.$event->profile->name) : asset('general/slide_ticketland.png');
                    $startDate                  = DateFormatTrait::parseDate($event->eventDates[0]->date, '/', 'monthsAbrev');
                    $endDate                    = DateFormatTrait::parseDate($event->eventDates[sizeof($event->eventDates) - 1]->date, '/', 'monthsAbrev');
                    $data[$pos]['dates']        = $startDate.' al '.$endDate;
                    $data[$pos]['currentDate']  = DateFormatTrait::parseDate(date('Y-m-d'), '/', 'monthsAbrev').' '.date('h:i A');
                    $data[$pos]['promotion']    = $promotion;
                    $data[$pos]['code']         = $discount['code'];
                    $data[$pos]['price']        = $price;
                    $folio                       = strtoupper(uniqid());
                    $folioCrypt                  = Crypt::encrypt($folio);
                    $qr_code                     = QrCode::backgroundColor(255, 125, 0, 0.5)->size(800)->format('svg')->generate($folioCrypt);
                    $data[$pos]['qr_code']       = base64_encode($qr_code);
                    $data[$pos]['customer_name'] = $input['name'];
                    $data[$pos]['email']         = $input['email'];
                    $data[$pos]['phone']         = $input['phone'];
                    if ($input['question'][0]['id'] !== null && !empty($input['question'][0]['response'])) {
                        $data[$pos]['question'][] = $input['question'][0]['response'];
                    }
                    if ($input['question'][1]['id'] !== null && !empty($input['question'][1]['response'])) {
                        $data[$pos]['question'][] = $input['question'][1]['response'];
                    }
                    if ($input['question'][2]['id'] !== null && !empty($input['question'][2]['response'])) {
                        $data[$pos]['question'][] = $input['question'][2]['response'];
                    }
                    if ($input['question'][3]['id'] !== null && !empty($input['question'][3]['response'])) {
                        $data[$pos]['question'][] = $input['question'][3]['response'];
                    }
                    if ($input['question'][4]['id'] !== null && !empty($input['question'][4]['response'])) {
                        $data[$pos]['question'][] = $input['question'][4]['response'];
                    }
                    $pdf = PDF::setOptions([
                        'isRemoteEnabled' => true,
                    ])->loadView('pdfTicket', $data[$pos]);
                    $pdf->save('events/pdf/'.$event->id.'/'.$folio.'.pdf');
                    $files[$pos] = $folio;
                    $pos++;
                }
            }

            return ['success' => true, 'files' => $files];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_pdf.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear pdf: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => false,
                'msj'     => 'Error al crear tus boletos, si el problema persiste contacta al organizador del evento.<br>No se realizaron cargos.'
            ];
        }
    }

    public static function deleteFiles($event_id, $files) {
        for ($i = 0; $i < sizeof($files); $i++) { 
            if (file_exists('events/pdf/'.$event_id.'/'.$files[$i].'.pdf')) {
                unlink('events/pdf/'.$event_id.'/'.$files[$i].'.pdf');
            }
        }
    }

    public static function createReference($event_id, $dataReference, $orderClientId) {
        try {
            $date                            = Carbon::now();
            $expirationDate                  = Carbon::parse($date->addDays(2)->format('Y-m-d 23:59:59'))->locale('es')->isoFormat('D MMMM Y');
            $expirationHour                  = Carbon::parse($date->addDays(2)->format('Y-m-d 23:59:59'))->locale('es')->isoFormat('H:mm');
            $dataReference['expirationDate'] = $expirationDate;
            $dataReference['expirationHour'] = $expirationHour;

            $pdf = PDF::loadView('pdfOxxo', $dataReference);
            if (!file_exists('events/pdf/'.$event_id)) {
                mkdir('events/pdf/'.$event_id, 0777, true);
            }
            $pdf->save('events/pdf/'.$event_id.'/reference'.$orderClientId.'.pdf');
            return ['success' => true];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_reference.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear pdf de la referencia: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => false,
                'msj'     => 'Error al crear tu referencia de pago, si el problema persiste contacta al organizador del evento.<br>No se realizaron cargos.'
            ];
        }
    }
}