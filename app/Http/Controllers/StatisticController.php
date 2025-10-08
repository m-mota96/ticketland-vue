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
            $payed = Access::whereDate('created_at', '=', $date->format('Y-m-d'))->whereHas('payment', function($query) use($event_id) {
                return $query->where('status', 'payed')->where('event_id', $event_id)->whereHas('event', function($query2) {
                    return $query2->where('user_id', auth()->user()->id);
                });
            })->count();
            $array_sales[$date->format('Y-m-d')] = $payed;
            $pending = Access::whereDate('created_at', '=', $date->format('Y-m-d'))->whereHas('payment', function($query) use($event_id) {
                return $query->where('status', 'pending')->where('event_id', $event_id)->whereHas('event', function($query2) {
                    return $query2->where('user_id', auth()->user()->id);
                });
            })->count();
            $array_pending[$date->format('Y-m-d')] = $pending;
            $expired = Access::whereDate('created_at', '=', $date->format('Y-m-d'))->whereHas('payment', function($query) use($event_id) {
                return $query->where('status', 'expired')->where('event_id', $event_id)->whereHas('event', function($query2) {
                    return $query2->where('user_id', auth()->user()->id);
                });
            })->count();
            $array_expired[$date->format('Y-m-d')] = $expired;
        }

        $ticketsDiscount = Access::wherehas('payment', function($query) {
            $query->whereNotNull('code')->where('status', 'payed');
        })->count();

        $ticketsNotDiscount = Access::wherehas('payment', function($query) {
            $query->whereNull('code')->where('status', 'payed');
        })->count();

        $ticketsPending = Access::wherehas('payment', function($query) {
            $query->where('status', 'pending');
        })->count();

        $ticketsExpired = Access::wherehas('payment', function($query) {
            $query->where('status', 'expired');
        })->count();

        $salesOxxo = Payment::selectRaw('IF(SUM(amount - ROUND(amount * (discount / 100))) IS NOT NULL, SUM(amount - ROUND(amount * (discount / 100))), 0) AS total')
        ->selectRaw('"Pago en Oxxo" AS type')
        ->where('status', 'payed')->where('type', 'oxxo')->first();
        $salesCard = Payment::selectRaw('IF(SUM(amount - ROUND(amount * (discount / 100))) IS NOT NULL, SUM(amount - ROUND(amount * (discount / 100))), 0) AS total')
        ->selectRaw('"Tarjeta de Débito/Crédito" AS type')
        ->where('status', 'payed')->where('type', 'card')->first();
        $salesPaypal = Payment::selectRaw('IF(SUM(amount - ROUND(amount * (discount / 100))) IS NOT NULL, SUM(amount - ROUND(amount * (discount / 100))), 0) AS total')
        ->selectRaw('"Paypal" AS type')
        ->where('status', 'payed')->where('type', 'paypal')->first();

        return ResponseTrait::response('', [
            'sales'              => $array_sales,
            'pending'            => $array_pending,
            'expired'            => $array_expired,
            'ticketsDiscount'    => $ticketsDiscount,
            'ticketsNotDiscount' => $ticketsNotDiscount,
            'ticketsPending'     => $ticketsPending,
            'ticketsExpired'     => $ticketsExpired,
            'amounts'            => [$salesCard, $salesOxxo, $salesPaypal],
        ]);
    }
}
