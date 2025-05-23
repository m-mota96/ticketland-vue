<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\SendMailTrait;
use App\Models\Code;
use App\Models\Payment;
use App\Models\Ticket;

class WebHookController extends Controller {
    public function validatePayment(Request $request) {
        $body = @file_get_contents('php://input');
        $data = json_decode($body);
        if ($data->type == 'charge.paid') {
            $reference       = $data->data->object->payment_method->reference;
            // $reference       = $request->reference;
            $payment         = Payment::select('id', 'reference', 'code', 'status')->with(['accesses'])->where('reference', $reference)->first();
            $payment->status = 'payed';
            $payment->save();

            $code = null;
            if ($payment->code) {
                $code = Code::select('id', 'code', 'used', 'reserved')->where('code', $payment->code)->first();
                if ($code) {
                    $code->used     = $code->used + 1;
                    $code->reserved = $code->reserved - 1;
                    $code->save();
                }
            }

            $infoTickets = [];
            foreach ($payment->accesses as $key => $a) {
                $infoTickets[$a->ticket_id]['ticket_id'] = $a->ticket_id;
                $infoTickets[$a->ticket_id]['quantity']  = isset($infoTickets[$a->ticket_id]['quantity']) ? ($infoTickets[$a->ticket_id]['quantity'] + 1) : 1;
            }
            $infoTickets = array_values($infoTickets);
            
            for ($i = 0; $i < sizeof($infoTickets); $i++) { 
                $ticket = Ticket::select('id', 'sales', 'reserved')->find($infoTickets[$i]['ticket_id']);
                $ticket->reserved = $ticket->reserved - $infoTickets[$i]['quantity'];
                $ticket->sales    = $ticket->sales + $infoTickets[$i]['quantity'];
                $ticket->save();
            }

            SendMailTrait::index('tickets', $payment->id);

            return response()->json([
                true
            ]);
        }
    }
}
