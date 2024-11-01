<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventDate;
use App\Models\Ticket;
use App\Models\User;

class EventController extends Controller {
    public function index() {
        $categories = Category::all();
        return Inertia::render('Customer/Events', [
            'categories' => $categories
        ]);
    }

    public function createEvent(Request $request) {
        try {
            $event = Event::where('name', 'LIKE', '%'.$request->name.'%')->where('user_id', '<>', auth()->user()->id)->first();
            if ($event) {
                return response([
                    'error' => true,
                    'msj'   => 'El nombre del evento que intenta crear se encuentra en uso. Por favor contacte a soporte.'
                ], 409);
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
                'event_id' => $event->id,
                'name' => 'Boleto 1',
                'price' => ($request->type == 'paid') ? 100 : 0,
                'quantity' => 50,
                'valid' => $request->days,
                'start_sale' => date('Y-m-d'),
                'stop_sale' => $request->allDates[sizeof($request->allDates) - 1],
            ]);

            DB::commit();
            return response([
                'error' => false,
                'data'  => $event,
                'msj'   => 'El evento se creo correctamente'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'error' => true,
                'data'  => 'Ocurrio un error '.$th->getMessage(),
                'msj'   => 'Lo sentimos ocurrio un error. Si el problema persiste contacte a soporte'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'data'  => 'Ocurrio un error '.$e->getMesssage(),
                'msj'   => 'Lo sentimos ocurrio un error. Si el problema persiste contacte a soporte'
            ], 500);
        }
    }
}
