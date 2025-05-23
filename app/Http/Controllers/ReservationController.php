<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\SendMailTrait;
use App\Models\Event;
use App\Models\Payment;

class ReservationController extends Controller {
    public function reservations($event_id) {
        $event = Event::find($event_id);
        return Inertia::render('Customer/Event/Reservation', [
            'event'   => $event
        ]);
    }

    public function getReservations(Request $request) {
        try {
            $search = $request->search;
            $where = 'event_id = '.$request->event_id;
            if ($search['email']) {
                $where .= ' AND name LIKE "%'.$search['name'].'%"';
            }
            if ($search['email']) {
                $where .= ' AND email LIKE "%'.$search['email'].'%"';
            }
            if ($search['phone']) {
                $where .= ' AND phone LIKE "%'.$search['phone'].'%"';
            }
            if ($search['type'] !== 'all') {
                $where .= ' AND type = "'.$search['type'].'"';
            }
            if ($search['status'] !== 'all') {
                $where .= ' AND status = "'.$search['status'].'"';
            }

            $pagination = $request->pagination;
            $page       = $pagination['currentPage']; // Página actual
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $offset     = ($page - 1) * $limit; // Calcular el offset

            $payments    = Payment::with(['accesses.ticket'])
            ->whereRaw($where)->orderBy($request->order['orderBy'], $request->order['order'])
            ->offset($offset)->limit($limit)->get();
            $allPayments = Payment::where('event_id', $request->event_id)->count();
            // dd($payments);
            return ResponseTrait::response(null, ['payments' => $payments, 'count' => $allPayments]);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } 
    }

    public function resendEmail(Request $request) {
        try {
            $payment = Payment::select('id', 'email', 'status')->find($request->payment_id);
            if ($payment->email !== $request->email) {
                $payment->email = $request->email;
                $payment->save();
            }
            switch ($payment->status) {
                case 'payed':
                    $txt      = 'Lob boletos se reenviaron correctamente';
                    $proccess = SendMailTRait::index('tickets', $payment->id);
                    break;
                case 'pending':
                    $txt      = 'La ficha de pago se reenvio correctamente';
                    $proccess = SendMailTRait::index('reference', $payment->id);
                    break;
            }

            if ($proccess['error']) {
                return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$tproccess['msj'], true, 500);
            }
            return ResponseTrait::response($txt);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
