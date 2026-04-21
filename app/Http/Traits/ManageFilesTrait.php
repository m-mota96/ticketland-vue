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
    public static function createPdf($tickets, $event, $code) {
        try {
            if (!file_exists('events/pdf/'.$event->id)) {
                mkdir('events/pdf/'.$event->id, 0777, true);
            }

            $files = [];

            for ($i = 0; $i < sizeof($tickets); $i++) {
                $ticket                      = Ticket::select('id', 'name', 'price', DB::raw('IF(CURDATE() >= date_promotion, NULL, promotion) promotion'))->find($tickets[$i]['id']);
                $price                       = $ticket->price;
                if ($ticket->promotion && !$code) {
                    $price = $ticket->price - round($ticket->price * ($ticket->promotion / 100));
                }
                $tickets[$i]['eventName']    = $event->name;
                $tickets[$i]['eventDesc']    = $event->description;
                $tickets[$i]['eventAddress'] = $event->location ? $event->location->address : 'Sin información';
                $tickets[$i]['eventProfile'] = $event->profile ? asset('events/images/'.$event->profile->name) : asset('general/slide_ticketland.png');
                $startDate                   = DateFormatTrait::parseDate($event->eventDates[0]->date, '/', 'monthsAbrev');
                $endDate                     = DateFormatTrait::parseDate($event->eventDates[sizeof($event->eventDates) - 1]->date, '/', 'monthsAbrev');
                $tickets[$i]['dates']        = $startDate.' al '.$endDate;
                $tickets[$i]['currentDate']  = DateFormatTrait::parseDate(date('Y-m-d'), '/', 'monthsAbrev');
                $tickets[$i]['promotion']    = $ticket->promotion;
                $tickets[$i]['price']        = number_format($price);
                $folio                       = strtoupper(uniqid());
                $folioCrypt                  = Crypt::encrypt($folio);
                $qr_code                     = QrCode::backgroundColor(255, 125, 0, 0.5)->size(800)->format('svg')->generate($folioCrypt);
                $tickets[$i]['qr_code']      = base64_encode($qr_code);
                $pdf                         = PDF::setOptions([
                    'isRemoteEnabled' => true,
                ])->loadView('pdfTicket', $tickets[$i]);
                $pdf->save('events/pdf/'.$event->id.'/'.$folio.'.pdf');
                $files[] = $folio;
                // $folio = Crypt::decrypt($folio);
            }

            return ['success' => true, 'files' => $files];
        } catch (\Throwable $th) {
            $logFile = fopen("logs/log_pdf.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear pdf: ".$th->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => false,
                'msj'     => 'Error al crear los archivos de sus boletos, si el problema persiste contacta al organizador del evento.<br>No se realizaron cargos.'
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