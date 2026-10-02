<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $results = Access::query()
        ->join('payments', 'payments.id', '=', 'accesses.payment_id')
        ->whereBetween('accesses.created_at', [
            $start_date->copy()->startOfDay(),
            $end_date->copy()->endOfDay(),
        ])
        ->where('payments.event_id', $event_id)
        ->whereIn('payments.status', ['payed', 'pending', 'expired'])
        ->selectRaw("
            DATE(accesses.created_at) AS date,
            payments.status,
            COUNT(DISTINCT accesses.unification) + SUM(accesses.unification IS NULL) AS total
        ")
        ->groupBy(
            DB::raw('DATE(accesses.created_at)'),
            'payments.status'
        )
        ->get();

        foreach ($results as $row) {
            $day = Carbon::parse($row->date)->day;
            
            switch ($row->status) {
                case 'payed':
                    $array_sales[$day] = (int) $row->total;
                    break;

                case 'pending':
                    $array_pending[$day] = (int) $row->total;
                    break;

                case 'expired':
                    $array_expired[$day] = (int) $row->total;
                    break;
            }
        }

        $ticketsDiscount = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'payed')->where('event_id', $event_id);
        })
        ->selectRaw('COUNT(DISTINCT unification) + SUM(unification IS NULL) AS total')
        ->where(function ($query) {
            $query->whereNotNull('code_discount')->orWhereNotNull('accesses.promotion');
        })->value('total');

        $ticketsNotDiscount = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'payed')->where('event_id', $event_id);
        })
        ->selectRaw('COUNT(DISTINCT unification) + SUM(unification IS NULL) AS total')
        ->whereNull('code_discount')->whereNull('accesses.promotion')->value('total');

        $ticketsPending = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'pending')->where('event_id', $event_id);
        })
        ->selectRaw('COUNT(DISTINCT unification) + SUM(unification IS NULL) AS total')
        ->value('total');

        $ticketsExpired = Access::whereHas('payment', function ($query) use ($event_id) {
            $query->where('status', 'expired')->where('event_id', $event_id);
        })
        ->selectRaw('COUNT(DISTINCT unification) + SUM(unification IS NULL) AS total')
        ->value('total');

        $sales = Payment::with(['paymentMethod:id,name'])
        ->select('payment_method_id')
        ->selectRaw("SUM(amount) AS total")
        ->where('status', 'payed')->where('event_id', $event_id)->groupBy('payment_method_id')->get();

        return ResponseTrait::response('', [
            'sales'              => $array_sales,
            'pending'            => $array_pending,
            'expired'            => $array_expired,
            'ticketsDiscount'    => (int) $ticketsDiscount ?? 0,
            'ticketsNotDiscount' => (int) $ticketsNotDiscount ?? 0,
            'ticketsPending'     => (int) $ticketsPending ?? 0,
            'ticketsExpired'     => (int) $ticketsExpired ?? 0,
            'amounts'            => $sales,
        ]);
    }
}
