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
        $tickets = Ticket::with(['event'])->where('event_id', $id)->get();
        return Inertia::render('Customer/Event/Ticket', [
            'event'   => $event,
            'tickets' => $tickets
        ]);
    }

    public function createTicket(Request $request) {
        try {
            //code...
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function editTicket(Request $request) {
        try {
            $ticket = Ticket::find($request->id);
            return ResponseTrait::response('El boleto se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
