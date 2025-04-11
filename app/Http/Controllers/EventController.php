<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Access;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventDate;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Http\Traits\ResponseTrait;

class EventController extends Controller {
    public function index() {
        $categories = Category::orderBy('name')->get();
        return Inertia::render('Customer/Events', [
            'categories' => $categories
        ]);
    }

    public function getEvents(Request $request) {
        try {
            $page   = $request->currentPage; // Página actual
            $limit  = $request->sizePage; // Tamaño de la página
            $offset = ($page - 1) * $limit; // Calcular el offset

            $where = 'user_id = '.auth()->user()->id;
            $where = ($request->status !== null) ? $where.' AND status = '.$request->status : $where;
            $where = (!empty($request->search)) ? $where.' AND name LIKE "%'.$request->search.'%"' : $where;
            $order = ($request->status == 1) ? 'ASC' : 'DESC';

            $events   = Event::with(['profile', 'eventDates'])
            ->addSelect(['quantity_tickets' => Ticket::selectRaw('SUM(quantity) as quantity')
                ->whereColumn('event_id', 'events.id')
                ->groupBy('event_id')
            ])->addSelect(['date' => EventDate::select('date')
                ->whereColumn('event_id', 'events.id')
                ->orderBy('date')->limit(1)
            ])->addSelect(['sales' => Access::whereHas('payment', function($query) {
                $query->whereColumn('event_id', 'events.id')->where('status', 'payed');
            })->selectRaw('COUNT(*)')])
            ->whereRaw($where)->orderBy('date', $order)->offset($offset)->limit($limit)->get();

            $active   = Event::where('user_id', auth()->user()->id)->where('status', 1)->count();
            $inactive = Event::where('user_id', auth()->user()->id)->where('status', 0)->count();
            $past     = Event::where('user_id', auth()->user()->id)->where('status', 2)->count();
            $all      = Event::where('user_id', auth()->user()->id)->count();

            $data  = [
                'events' => $events,
                'count'  => [
                    'active'   => $active,
                    'inactive' => $inactive,
                    'past'     => $past,
                    'all'      => $all
                ]
            ];
            return ResponseTrait::response(null, $data);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function createEvent(Request $request) {
        try {
            $event = Event::where('name', 'LIKE', '%'.$request->name.'%')->where('user_id', '<>', auth()->user()->id)->first();
            if ($event) {
                return ResponseTrait::response('El nombre del evento que intenta crear se encuentra en uso.<br>Por favor contacte a soporte.', null, true, 409);
            }

            DB::beginTransaction();
            $event = Event::create([
                'user_id'     => auth()->user()->id,
                'category_id' => $request->category,
                'name'        => trim($request->name),
                'url'         => trim($request->website),
                'description' => trim($request->description),
                'quantity'    => trim($request->capacity),
                'cost_type'   => $request->type
            ]);

            foreach ($request->allDates as $key => $d) {
                $dates[] = [
                    'event_id'     => $event->id,
                    'date'         => $d,
                    'initial_time' => $request->startHour[$key].':'.$request->startMinute[$key],
                    'final_time'   => $request->endHour[$key].':'.$request->endMinute[$key],
                ];
            }
            EventDate::insert($dates);

            Ticket::create([
                'event_id'   => $event->id,
                'name'       => 'Boleto 1',
                'price'      => ($request->type == 'paid') ? 100 : 0,
                'quantity'   => 50,
                'valid'      => $request->days,
                'start_sale' => date('Y-m-d'),
                'stop_sale'  => $request->allDates[sizeof($request->allDates) - 1],
            ]);

            DB::commit();
            return ResponseTrait::response('El evento se creo correctamente.', $event);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function changeStatusEvent(Request $request) {
        try {
            $event         = Event::find($request->event_id);
            $event->status = ($request->status) ? 1 : 0;
            $event->save();

            return ResponseTrait::response('El estatus se cambio correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function event($id) {
        $event      = Event::with(['profile', 'logo', 'eventDates', 'location', 'category'])->where('id', $id)->first();
        $categories = Category::orderBy('name')->get();
        return Inertia::render('Customer/Event', [
            'event'      => $event,
            'categories' => $categories
        ]);
    }

    public function editEvent(Request $request) {
        try {
            $event               = Event::find($request->id);
            $event->name         = trim($request->name);
            $event->url          = trim($request->url);
            $event->quantity     = trim($request->quantity);
            $event->category_id  = trim($request->category_id);
            $event->save();
            return ResponseTrait::response('La información se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function uploadImages(Request $request) {
        try {
            dd($request->all());
            return ResponseTrait::response('La imagen se cargo correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
