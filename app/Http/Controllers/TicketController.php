<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;
use App\Models\Ticket;

class TicketController extends Controller {
    public function tickets($event_id) {
        $event = Event::with(['eventDates'])->find($event_id);
        return Inertia::render('Customer/Event/Ticket', [
            'event'   => $event
        ]);
    }

    public function getTickets(Request $request) {
        try {
            $tickets = Ticket::with(['event', 'access' => function($query) {
                $query->with('payment')->whereHas('payment', function($query2) {
                    $query2->where('status', 'payed');
                });
            }])
            ->where('event_id', $request->event_id)->orderBy('order')->get();
            return ResponseTrait::response(null, $tickets);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function createTicket(Request $request) {
        try {
            $order = Ticket::selectRaw('IF(MAX(`order`) IS NULL , 0, MAX(`order`)) AS `order`')->where('event_id', $request->event_id)->first();
            $order = $order->order + 1;
            Ticket::create([
                'event_id'        => $request->event_id,
                'name'            => trim($request->name),
                'description'     => trim($request->description),
                'valid'           => $request->valid,
                'promotion'       => $request->discount ? $request->promotion : null,
                'date_promotion'  => $request->discount ? $request->date_promotion : null,
                'start_sale'      => $request->start_sale,
                'stop_sale'       => $request->stop_sale,
                'price'           => $request->cost_type === 'paid' ? $request->price : null,
                'min_reservation' => 1,
                'max_reservation' => 10,
                'quantity'        => $request->quantity,
                'order'           => $order
            ]);
            return ResponseTrait::response('El boleto se creó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function editTicket(Request $request) {
        try {
            if ($request->type === 'info') {
                $ticket = Ticket::find($request->ticket_id);
                $ticket->name            = trim($request->name);
                $ticket->description     = trim($request->description);
                $ticket->valid           = $request->valid;
                $ticket->promotion       = $request->discount ? $request->promotion : null;
                $ticket->date_promotion  = $request->discount ? $request->date_promotion : null;
                $ticket->start_sale      = $request->start_sale;
                $ticket->stop_sale       = $request->stop_sale;
                $ticket->price           = $request->cost_type === 'paid' ? $request->price : null;
                $ticket->min_reservation = 1;
                $ticket->max_reservation = 10;
                $ticket->quantity        = $request->quantity;
                $ticket->save();
            } else {
                foreach ($request->orderTickets as $key => $t) {
                    $ticket        = Ticket::find($t['id']);
                    $ticket->order = $t['order'];
                    $ticket->save();
                }
            }
            return ResponseTrait::response('El boleto se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function ticketStatus(Request $request) {
        try {
            $txt            = $request->status == 1 ? 'activó' : 'desactivó';
            $ticket         = Ticket::find($request->id);
            $ticket->status = $request->status;
            $ticket->save();
            return ResponseTrait::response('El boelto se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteTicket(Request $request) {
        try {
            $ticket = Ticket::with(['access' => function($query) {
                $query->with('payment')->whereHas('payment', function($query2) {
                    $query2->where('status', 'payed');
                });
            }])->where('id', $request->ticket_id)->first();
            if (sizeof($ticket->access) > 0) {
                return ResponseTrait::response('No se puede eliminar el boleto porque ya tiene ventas.', null, true, 409);
            }
            $ticket->delete();
            return ResponseTrait::response('El boleto se eliminó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
