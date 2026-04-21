<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\Access;
use App\Models\Code;
use App\Models\Event;

class InfluencerController extends Controller {
    public function getCodes(Request $request) {
        try {
            switch ($request->type) {
                case 'events':
                    $events = Event::select('id', 'name')
                    ->where('status', 1)
                    ->whereHas('codes', function($q) use ($request) {
                        $q->where('email', $request->email);
                    })->get();
                    return ResponseTrait::response(null, $events);
                case 'codes':
                    $codes = Code::select('id', 'code', 'customer_name')
                    ->selectRaw('IF(expiration IS NULL, "N/A", date_format(expiration, "%d/%m/%Y")) AS expiration')
                    ->addSelect(['total' => Access::selectRaw('SUM((price * (code_discount / 100)) * (.10))')
                        ->whereColumn('code_id', 'codes.id')
                        ->groupBy('code_id')
                    ])
                    ->with(['accesses:id,code_id,name,price,code_discount', 'accesses' => function($q) {
                        $q->whereHas('payment', function($q2) {
                            $q2->where('status', 'payed');
                        });
                    }])
                    ->where('email', $request->email)
                    ->where('event_id', $request->event_id)
                    ->whereHas('event', function($q) {
                        $q->where('status', 1);
                    })
                    ->get();
                    return ResponseTrait::response(null, $codes);
                default:
                    return ResponseTrait::response('No se encuentra el recurso solicitado.', null, true, 409);
            }
            
        } catch (\Throwable $th) {
            return ResponseTrait::response('Ocurrió un error. Si el problema persiste contacta a soporte.', $th->getMessage(), true, 500);
        }
    }
}
