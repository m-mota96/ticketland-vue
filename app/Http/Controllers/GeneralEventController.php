<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\ValidateCodesTrait;
use App\Http\Traits\ValidateStockTrait;
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
            DB::beginTransaction();

            $proccess = ValidateStockTrait::validateStock($request->tickets);
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response($proccess['msj'], ['error' => $proccess['error'], 'code' => $proccess['code']], true, $proccess['code']);
            }

            $proccess = ValidateCodesTrait::validateCodes($request->informationTickets);
            if (!$proccess['success']) {
                DB::rollBack();
                return ResponseTrait::response($proccess['msj'], ['error' => $proccess['error'], 'code' => $proccess['code']], true, $proccess['code']);
            }

            // DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function verifyCodes(Request $request) {
        try {
            // DB::beginTransaction();
            
            $proccess = ValidateCodesTrait::validateCodes($request->all(), false);
            if (!$proccess['success']) {
                // DB::rollBack();
                return ResponseTrait::response($proccess['msj'], ['data' => $proccess['data'],'error' => $proccess['error'], 'code' => $proccess['code']], true, $proccess['code']);
            }
            return ResponseTrait::response('', ['data' => $proccess['data']]);
        } catch (\Throwable $th) {
            // DB::rollBack();
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte al organizador del evento.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
