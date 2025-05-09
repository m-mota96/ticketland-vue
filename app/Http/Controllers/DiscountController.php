<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Code;
use App\Models\Event;
use App\Models\Ticket;

class DiscountController extends Controller {
    public function discounts($event_id) {
        $event   = Event::with(['eventDates'])->find($event_id);
        $tickets = Ticket::orderBy('name')->get();
        return Inertia::render('Customer/Event/Discount', [
            'event'   => $event,
            'tickets' => $tickets
        ]);
    }

    public function getDiscounts(Request $request) {
        try {
            $codes = Code::with(['tickets'])
            ->whereHas('tickets', function($query) use($request) {
                $query->where('event_id', $request->event_id);
            })
            ->orderBy('code')->get();
            return ResponseTrait::response(null, $codes);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function createDiscount(Request $request) {
        try {
            $code = Code::create([
                'code'       => trim($request->code),
                'discount'   => $request->discount,
                'quantity'   => $request->quantity,
                'expiration' => $request->expiration
            ]);
            $code->tickets()->sync($request->tickets);
            return ResponseTrait::response('El código se creo correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editDiscount(Request $request) {
        try {
            $code = Code::find($request->id);
            $code->code       = trim($request->code);
            $code->discount   = $request->discount;
            $code->quantity   = $request->quantity;
            $code->expiration = $request->expiration;
            $code->save();
            $code->tickets()->sync($request->tickets);
            return ResponseTrait::response('El código se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function discountStatus(Request $request) {
        try {
            $txt          = $request->status == 1 ? 'activo' : 'desactivo';
            $code         = Code::find($request->id);
            $code->status = $request->status;
            $code->save();
            return ResponseTrait::response('El código se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteDiscount(Request $request) {
        try {
            $code = Code::with(['tickets'])->find($request->id);
            foreach ($code->tickets as $key => $t) {
                if ($t->pivot->used > 0 || $t->pivot->reserved > 0) {
                    return ResponseTrait::response('No se puede eliminar el código porque ya ha sido utilizado.', null, true, 409);
                }
            }
            $code->tickets()->detach();
            $code->delete();
            return ResponseTrait::response('El código se eliminó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
