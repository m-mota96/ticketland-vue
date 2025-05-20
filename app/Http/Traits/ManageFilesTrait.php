<?php

namespace App\Http\Traits;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\DateFormatTrait;
use App\Models\Ticket;

trait ManageFilesTrait {
    public static function createPdf($tickets, $event) {
        if (!file_exists('events/pdf/'.$event->id)) {
            mkdir('events/pdf/'.$event->id, 0777, true);
        }

        $files = [];

        for ($i = 0; $i < sizeof($tickets); $i++) {
            $ticket                      = Ticket::select('id', 'name', 'price')->find($tickets[$i]['id']);
            $tickets[$i]['eventName']    = $event->name;
            $tickets[$i]['eventDesc']    = $event->description;
            $tickets[$i]['eventAddress'] = $event->location ? $event->location->address : 'Sin información';
            $tickets[$i]['eventProfile'] = $event->profile ? 'events/images/'.$event->profile->name : 'general/not_image.png';
            $startDate                   = DateFormatTrait::parseDate($event->eventDates[0]->date, '/', 'monthsAbrev');
            $endDate                     = DateFormatTrait::parseDate($event->eventDates[sizeof($event->eventDates) - 1]->date, '/', 'monthsAbrev');
            $tickets[$i]['dates']        = $startDate.' al '.$endDate;
            $tickets[$i]['currentDate']  = DateFormatTrait::parseDate(date('Y-m-d'), '/', 'monthsAbrev');
            $tickets[$i]['price']        = number_format($ticket->price);
            $folio                       = strtoupper(uniqid());
            $folioCrypt                  = Crypt::encrypt($folio);
            $qr_code                     = QrCode::backgroundColor(255, 125, 0, 0.5)->size(800)->format('svg')->generate($folioCrypt);
            $tickets[$i]['qr_code']      = base64_encode($qr_code);
            $pdf                         = PDF::loadView('pdfTicket', $tickets[$i]);
            $pdf->save('events/pdf/'.$event->id.'/'.$folio.'.pdf');
            $files[] = $folio;
            // $folio = Crypt::decrypt($folio);
        }

        return ['success' => true, 'files' => $files];
    }

    public static function deleteFiles($event_id, $files) {
        for ($i = 0; $i < sizeof($files); $i++) { 
            if (file_exists('events/pdf/'.$event_id.'/'.$files[$i].'.pdf')) {
                unlink('events/pdf/'.$event_id.'/'.$files[$i].'.pdf');
            }
        }
    }
}