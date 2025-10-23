<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\ReservationsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\SendMailTrait;
use App\Models\Event;
use App\Models\Payment;
use ZipArchive;

class ReservationController extends Controller {
    public function reservations($event_id) {
        $event = Event::find($event_id);
        return Inertia::render('Customer/Event/Reservation', [
            'event'   => $event
        ]);
    }

    public function getReservations(Request $request) {
        try {
            $pagination = $request->pagination;
            $page       = $pagination['currentPage']; // Página actual
            $limit      = $pagination['pageSize']; // Tamaño de la página
            $offset     = ($page - 1) * $limit; // Calcular el offset
            $search     = $request->search;
            $query      = Payment::with(['accesses.ticket'])->where('event_id', $request->event_id);

            if ($search['name']) {
                $query->whereRaw('name LIKE "%'.$search['name'].'%"');
            }
            if ($search['email']) {
                $query->whereRaw('email LIKE "%'.$search['email'].'%"');
            }
            if ($search['phone']) {
                $query->whereRaw('phone LIKE "%'.$search['phone'].'%"');
            }
            if ($search['type'] !== 'all') {
                $query->where('type', $search['type']);
            }
            if ($search['status'] !== 'all') {
                $query->where('status', $search['status']);
            }

            $payments = $query->orderBy($request->order['orderBy'], $request->order['order'])
            ->offset($offset)->limit($limit)
            ->get();
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

    public function downloadTickets(Request $request) {
        try {
            if (!file_exists('events/zips/'.$request->event_id)) {
                mkdir('events/zips/'.$request->event_id, 0777, true);
            }
            $payment = Payment::with(['accesses:id,payment_id,ticket_id,name,folio', 'accesses.ticket:id,name'])->select('id', 'event_id', 'name')->find($request->payment_id);
            
            $zip = new ZipArchive();
            $filename = 'events/zips/'.$payment->event_id.'/'.$payment->id.'_'.$payment->name.'.zip';
            if (file_exists($filename)) {
                unlink($filename);
            }

            if(!$zip->open($filename, ZIPARCHIVE::CREATE)) {
                return ResponseTrait::response('Error al crear archivo zip.', 'Ocurrio un error '.$th->getMessage(), true, 500);
            }

            foreach ($payment->accesses as $key => $a) {
                $url = 'events/pdf/'.$payment->event_id.'/'.$a->folio.'.pdf';
                $zip->addFile($url, ($key + 1).' '.$a->ticket->name.' '.$a->name.'.pdf');
            }
            $zip->close();
            return ResponseTrait::response(null, ['fileName' => $filename.'?v='.uniqid()]);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function downloadReservations($event_id) {
        try {
            return Excel::download(new ReservationsExport($event_id), 'Reservaciones.xlsx');
        } catch (\Throwable $th) {
            return redirect(route('cliente.reservaciones', $event_id));
        }
    }
}
