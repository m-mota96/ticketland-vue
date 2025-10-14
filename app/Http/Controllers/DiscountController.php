<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Code;
use App\Models\Event;
use App\Models\Ticket;
use GuzzleHttp\Client;

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
            $codes = Code::with(['payments' => function($query) {
                $query->where('status', 'payed');
            }, 'payments.accesses'])->where('event_id', $request->event_id)->orderBy('code')->get();
            // foreach ($codes as $key => $c) {
            //     foreach ($c->tickets as $key2 => $t) {
            //         $c->used = $c->used + $t->pivot->used;
            //     }
            // }
            return ResponseTrait::response(null, $codes);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function createDiscount(Request $request) {
        try {
            if ($request->influencer && $request->email && $request->password && $request->password_confirm) {
                if ($request->password !== $request->password_confirm) {
                    return ResponseTrait::response('Las contraseñas no coinciden.', null, true, 409);
                }
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', 'https://influencer.ticketland.mx/api/registro', [
                    'form_params' => [ 'name' => trim($request->influencer), 'email' => trim($request->email), 'password' => $request->password ]
                ]);
                // Obtener el cuerpo de la respuesta
                $body = $response->getBody();
                $content = $body->getContents();
                
                // Si la respuesta es JSON, decodificarla
                $data = json_decode($content, true);

                if ($data['error']) {
                    return ResponseTrait::response('La empresa o influencer ya esta registrado/a.<br>Por favor borra las contraseñas.', null, true, 409);
                }
            }
            $code = Code::create([
                'event_id'      => $request->event_id,
                'email'         => $request->email,
                'customer_name' => $request->influencer,
                'code'          => trim($request->code),
                'discount'      => $request->discount,
                'quantity'      => $request->quantity,
                'expiration'    => $request->expiration
            ]);
            // $code->tickets()->sync($request->tickets);
            return ResponseTrait::response('El código se creó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editDiscount(Request $request) {
        try {
            if ($request->influencer && $request->email && $request->password && $request->password_confirm) {
                if ($request->password !== $request->password_confirm) {
                    return ResponseTrait::response('Las contraseñas no coinciden.', null, true, 409);
                }
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', 'https://influencer.ticketland.mx/api/registro', [
                    'form_params' => [ 'name' => trim($request->influencer), 'email' => trim($request->email), 'password' => $request->password ]
                ]);
                // Obtener el cuerpo de la respuesta
                $body    = $response->getBody();
                $content = $body->getContents();
                
                // Si la respuesta es JSON, decodificarla
                $data = json_decode($content, true);

                if ($data['error']) {
                    return ResponseTrait::response('La empresa/influencer ya esta registrado/a.<br>Por favor borra las contraseñas.', null, true, 409);
                }
            }
            $code                = Code::find($request->id);
            $code->email         = $request->email;
            $code->customer_name = $request->influencer;
            $code->code          = trim($request->code);
            $code->discount      = $request->discount;
            $code->quantity      = $request->quantity;
            $code->expiration    = $request->expiration;
            $code->save();
            // $code->tickets()->sync($request->tickets);
            return ResponseTrait::response('El código se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function discountStatus(Request $request) {
        try {
            $txt          = $request->status == 1 ? 'activó' : 'desactivó';
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
            // foreach ($code->tickets as $key => $t) {
            //     if ($t->pivot->used > 0 || $t->pivot->reserved > 0) {
            //         return ResponseTrait::response('No se puede eliminar el código porque ya ha sido utilizado.', null, true, 409);
            //     }
            // }
            // $code->tickets()->detach();
            if ($code->used > 0 || $code->reserved > 0) {
                return ResponseTrait::response('No se puede eliminar el código porque ya ha sido utilizado.', null, true, 409);
            }
            $code->delete();
            return ResponseTrait::response('El código se eliminó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
