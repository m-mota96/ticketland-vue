<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;

class ValidateEvent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $action): Response
    {
        $event_id = (!empty($request->id)) ? $request->id : $request->event_id;

        $event = Event::where('id', $event_id)->where('user_id', auth()->user()->id)->first();
        if (!$event) {
            if ($action == 'view') {
                return redirect(route('cliente.mis_eventos'));
            }
            return ResponseTrait::response('No tiene permisos para modificar este evento.', null, true, 409);
        }

        return $next($request);
    }
}
