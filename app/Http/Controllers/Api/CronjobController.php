<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;

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
}
