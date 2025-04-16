<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;
use App\Models\Ticket;

class TicketController extends Controller {
    public function tickets($id) {
        $event   = Event::with(['eventDates'])->find($id);
        $tickets = Ticket::where('event_id', $id)->get();
        return Inertia::render('Customer/Event/Ticket', [
            'event'   => $event,
            'tickets' => $tickets
        ]);
    }
}
