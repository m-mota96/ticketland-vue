<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\CodesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ResponseTrait;
use App\Models\Code;
use App\Models\Event;
use GuzzleHttp\Client;

class DiscountController extends Controller {
    public function discounts($event_id) {
        $event   = Event::with(['eventDates'])->find($event_id);
        return Inertia::render('Customer/Event/Discount', [
            'event'   => $event
        ]);
    }

    public function getDiscounts(Request $request) {
        try {
            $codes = Code::with(['tickets:id,name', 'accesses' => function($q) {
                $q->whereHas('payment', function($q2) {
                    $q2->where('status', 'payed');
                });
            }])->where('event_id', $request->event_id)->orderBy('code')->get();
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
                $client   = new \GuzzleHttp\Client();
                $response = $client->request('POST', 'https://influencer.ticketland.mx/api/registro', [
                    'form_params' => [ 'name' => trim($request->influencer), 'email' => trim($request->email), 'password' => $request->password ]
                ]);
                // Obtener el cuerpo de la respuesta
                $body    = $response->getBody();
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
            $code->tickets()->sync($request->tickets);
            return ResponseTrait::response('El cupón se creó correctamente.');
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
                $client   = new \GuzzleHttp\Client();
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
            $code->tickets()->sync($request->tickets);
            return ResponseTrait::response('El cupón se modificó correctamente.');
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
            return ResponseTrait::response('El cupón se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function deleteDiscount(Request $request) {
        try {
            $code = Code::with(['tickets'])->find($request->id);
            if ($code->used > 0 || $code->reserved > 0) {
                return ResponseTrait::response('No se puede eliminar el cupón porque ya ha sido utilizado.', null, true, 409);
            }
            $code->delete();
            return ResponseTrait::response('El cupón se eliminó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function downloadCodes(Request $request) {
        try {
            $count = Code::where('event_id', $request->event_id)->whereNotNull('customer_name')->whereNotNull('email')->count();
            if (!$count) {
                return ResponseTrait::response('No hay información que exportar.', null, true, 409);
            }
            $fileName = 'Reporte de ganancias de influencers.xlsx';
            Excel::store(new CodesExport($request->event_id), '/excel/'.$request->event_id.'/'.$fileName, 'public');
            return ResponseTrait::response('Archivo generado correctamente.<br>Revisa tus descargas.', asset('storage/excel/'.$request->event_id.'/'.$fileName.'?v='.uniqid()));
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
