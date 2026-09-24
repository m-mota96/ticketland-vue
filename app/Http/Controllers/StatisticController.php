<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Access;
use App\Models\Event;
use App\Models\Payment;
use Carbon\Carbon;

class StatisticController extends Controller {
    public function statistics($event_id) {
        $event = Event::find($event_id);
        return Inertia::render('Customer/Event/Statistic', [
            'event'   => $event
        ]);
    }

    public function getStatistics(Request $request) {
        $start_date    = Carbon::parse($request->year.'-'.$request->month.'-01');
        $month         = $request->month;
        $year          = $request->year;
        $end_day       = date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));
        $end_date      = Carbon::parse($end_day);
        $array_sales   = [];
        $array_pending = [];
        $array_expired = [];
        $event_id      = $request->event_id;

        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $payed = Access::whereDate('accesses.created_at', $date->format('Y-m-d'))
            ->whereHas('payment', function ($query) use ($event_id) {
                $query->where('status', 'payed')
                ->where('event_id', $event_id);
            })
            ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
            ->selectRaw('
                COUNT(DISTINCT CASE
                    WHEN tickets.package = 1
                    THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
                END)
                +
                COUNT(CASE
                    WHEN tickets.package = 0
                    THEN accesses.id
                END) AS total
            ')
            ->value('total');
            $array_sales[intval($date->format('d'))] = $payed;

            $pending = Access::whereDate('accesses.created_at', $date->format('Y-m-d'))
            ->whereHas('payment', function ($query) use ($event_id) {
                $query->where('status', 'pending')
                ->where('event_id', $event_id);
            })
            ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
            ->selectRaw('
                COUNT(DISTINCT CASE
                    WHEN tickets.package = 1
                    THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
                END)
                +
                COUNT(CASE
                    WHEN tickets.package = 0
                    THEN accesses.id
                END) AS total
            ')
            ->value('total');
            $array_pending[intval($date->format('d'))] = $pending;

            $expired = Access::whereDate('accesses.created_at', $date->format('Y-m-d'))
            ->whereHas('payment', function ($query) use ($event_id) {
                $query->where('status', 'expired')
                ->where('event_id', $event_id);
            })
            ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
            ->selectRaw('
                COUNT(DISTINCT CASE
                    WHEN tickets.package = 1
                    THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
                END)
                +
                COUNT(CASE
                    WHEN tickets.package = 0
                    THEN accesses.id
                END) AS total
            ')
            ->value('total');
            $array_expired[intval($date->format('d'))] = $expired;
        }

        $ticketsDiscount = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'payed')->where('event_id', $event_id);
        })
        ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
        ->selectRaw('
            COUNT(DISTINCT CASE
                WHEN tickets.package = 1
                THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
            END)
            +
            COUNT(CASE
                WHEN tickets.package = 0
                THEN accesses.id
            END) AS total
        ')
        ->where(function ($query) {
            $query->whereNotNull('code_discount')->orWhereNotNull('accesses.promotion');
        })->value('total');

        $ticketsNotDiscount = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'payed')->where('event_id', $event_id);
        })
        ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
        ->selectRaw('
            COUNT(DISTINCT CASE
                WHEN tickets.package = 1
                THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
            END)
            +
            COUNT(CASE
                WHEN tickets.package = 0
                THEN accesses.id
            END) AS total
        ')
        ->whereNull('code_discount')->whereNull('accesses.promotion')->value('total');

        $ticketsPending = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'pending')->where('event_id', $event_id);
        })
        ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
        ->selectRaw('
            COUNT(DISTINCT CASE
                WHEN tickets.package = 1
                THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
            END)
            +
            COUNT(CASE
                WHEN tickets.package = 0
                THEN accesses.id
            END) AS total
        ')
        ->value('total');

        $ticketsExpired = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'expired')->where('event_id', $event_id);
        })
        ->join('tickets', 'tickets.id', '=', 'accesses.ticket_id')
        ->selectRaw('
            COUNT(DISTINCT CASE
                WHEN tickets.package = 1
                THEN CONCAT(accesses.payment_id, "-", accesses.ticket_id)
            END)
            +
            COUNT(CASE
                WHEN tickets.package = 0
                THEN accesses.id
            END) AS total
        ')
        ->value('total');

        // $sales = Payment::selectRaw("
        //     CASE type
        //         WHEN 'card' THEN 'Tarjeta de Débito/Crédito'
        //         WHEN 'oxxo' THEN 'Pago en Oxxo'
        //         WHEN 'paypal' THEN 'Paypal'
        //     END type
        // ")->selectRaw("
        //     SUM(IF(`code` IS NULL, amount, (amount - (amount * (discount / 100))))) total
        // ")->where('status', 'payed')->where('event_id', $event_id)->groupBy('type')->get();
        $sales = Payment::with(['paymentMethod:id,name'])
        ->select('payment_method_id')
        ->selectRaw("SUM(amount) AS total")
        ->where('status', 'payed')->where('event_id', $event_id)->groupBy('payment_method_id')->get();

        return ResponseTrait::response('', [
            'sales'              => $array_sales,
            'pending'            => $array_pending,
            'expired'            => $array_expired,
            'ticketsDiscount'    => $ticketsDiscount,
            'ticketsNotDiscount' => $ticketsNotDiscount,
            'ticketsPending'     => $ticketsPending,
            'ticketsExpired'     => $ticketsExpired,
            'amounts'            => $sales,
        ]);
    }
}
