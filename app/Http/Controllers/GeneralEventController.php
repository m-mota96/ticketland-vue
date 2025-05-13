<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;

class GeneralEventController extends Controller {
    public function event($url) {
        $event = Event::with(['tickets' => function($query) {
            $query->where('start_sale', '<=', date('Y-m-d'))->where('stop_sale', '>=', date('Y-m-d'));
        }, 'eventDates', 'profile', 'logo', 'location'])->where(DB::raw('BINARY url'), $url)->first();
        if (!$event) {
            return redirect('/');
        }
        return Inertia::render('Event/Event', [
            'event'   => $event
        ]);
    }

    public function makePayment(Request $request) {
        try {
            dd($request->all());
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
