<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Access;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use GuzzleHttp\Client;

class CronjobController extends Controller {
    public function ticketsExpired() {
        $payments = Payment::with(['accesses'])
        ->where('status', 'pending')
        ->whereRaw('created_at <= CURDATE() - INTERVAL 2 DAY')
        ->get();

        $infoTickets = [];
        foreach ($payments as $key => $p) {
            $p->status = 'expired';
            $p->save();
            foreach ($p->accesses as $key2 => $a) {
                $infoTickets[$a->ticket_id]['ticket_id'] = $a->ticket_id;
                $infoTickets[$a->ticket_id]['quantity']  = isset($infoTickets[$a->ticket_id]['quantity']) ? ($infoTickets[$a->ticket_id]['quantity'] + 1) : 1;
            }
        }
        $infoTickets = array_values($infoTickets);
        
        for ($i = 0; $i < sizeof($infoTickets); $i++) { 
            $ticket = Ticket::select('id', 'reserved')->find($infoTickets[$i]['ticket_id']);
            $ticket->reserved = $ticket->reserved - $infoTickets[$i]['quantity'];
            $ticket->save();
        }
        return response()->json([
            true
        ]);
    }

    public function disableEvents() {
        $events = Event::with(['eventDates'])->where('status', 1)->get();
        foreach ($events as $key => $e) {
            if ($e->eventDates[sizeof($e->eventDates) - 1]['date'] < date('Y-m-d')) {
                $e->status = 2;
                $e->save();
            }
        }
        return response()->json([
            true
        ]);
    }

    public function sendTicketsCrm() {
        $accesses = Access::select(
            'id',
            'ticket_id',
            'name',
            'email',
            'phone',
            'folio_encrypted',
            'saved_in_crm',
            'created_at'
        )
        ->with([
            'ticket:id,event_id,crm_event_id',
            'ticket.event:id,name,url',
            'responses:id,access_id,response'
        ])
        ->whereHas('ticket', function($q) {
            $q->whereNotNull('crm_event_id');
        })
        ->whereHas('responses')
        ->where('saved_in_crm', false)
        ->get();

        $client = new Client();

        $success = true;
        foreach ($accesses as $key => $a) {
            $dataConferences = [
                'event_id' => $a->ticket->crm_event_id,
                'code'     => $a->folio_encrypted,
                'name'     => $a->name,
                'email'    => $a->email,
                'password' => '123456789',
                'phone'    => $a->phone,
                'metadata' => [
                    'event'      => $a->ticket->event->name,
                    'url_base'   => 'https://ticketland.mx/evento/'.$a->ticket->event->url,
                    'table'      => 'accesses',
                    'id_table'   => $a->id,
                    'occupation' => $a->responses->first()->response,
                    'created_at' => $a->created_at
                ]
            ];

            try {
                $response = $client->post(
                    'https://conferences.api.maxwellcorp.mx/api/tickets/create',
                    [
                        'json' => $dataConferences,
                    ]
                );

                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    $a->saved_in_crm = true;
                    $a->save();
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                // Error de la petición
                $success = false;
                $logFile = fopen("logs/log_ticketCrm.txt", 'a') or die("Error creando archivo");
                fwrite($logFile, date("d/m/Y H:i:s")." Error al crear acceso en el CRM: ".$e->getMessage()." (access_id: ".$a->id.", name: ".$a->name.")\n") or die("Error escribiendo en el archivo");
                fclose($logFile);
            }
        }

        return response()->json([
            'success' => $success
        ]);
    }
}
