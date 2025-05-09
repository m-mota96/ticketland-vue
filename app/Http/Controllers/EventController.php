<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Access;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventDate;
use App\Models\GalleryEvent;
use App\Models\LocationEvent;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;

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

    public function event($event_id) {
        $event      = Event::with(['profile', 'logo', 'eventDates', 'location', 'category'])->where('id', $event_id)->first();
        $categories = Category::orderBy('name')->get();
        return Inertia::render('Customer/Event/Event', [
            'event'      => $event,
            'categories' => $categories
        ]);
    }

    public function editEvent(Request $request) {
        try {
            $event               = Event::find($request->event_id);
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
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png|max:1024', // tamaño en kilobytes (1024 KB = 1 MB)
            ]);

            $type            = ($request->type == 'profile') ? 'index' : 'logo';
            $file            = $request->file;
            $extension       = $file->getClientOriginalExtension();
            $fileName        = uniqid().'.'.$extension;
            $destinationPath = public_path('events/images');
            
            $gallery = GalleryEvent::where('event_id', $request->event_id)->where('type', $type)->first();

            DB::beginTransaction();
            if ($gallery) {
                if (file_exists('events/images/' . $gallery->name)) {
                    unlink('events/images/' . $gallery->name);
                }
                $gallery->name = $fileName;
                $gallery->save();
            } else {
                GalleryEvent::create([
                    'event_id' => $request->event_id,
                    'type'     => $type,
                    'name'     => $fileName
                ]);
            }

            // Crear carpeta si no existe
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Mover el archivo
            $file->move($destinationPath, $fileName);

            // URL pública del archivo
            $url = asset('events/images/' . $fileName);

            DB::commit();
            return ResponseTrait::response('La imagen se cargo correctamente.', $url);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseTrait::response($th->getMessage(), 'La imagen debe estar en formato<br>JPG o PNG y pesar menos de 1MB.', true, 422);
        }
    }

    public function deleteLogo(Request $request) {
        try {
            $logo = GalleryEvent::where('event_id', $request->event_id)->where('type', 'logo')->first();
            if ($logo) {
                if (file_exists('events/images/' . $logo->name)) {
                    unlink('events/images/' . $logo->name);
                }
                $logo->delete();
                return ResponseTrait::response('El logo se eliminó correctamente.');
            }
            return ResponseTrait::response('No es posible eliminar el logo.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editDescription(Request $request) {
        try {
            $event = Event::find($request->event_id);
            $event->description = $request->description;
            $event->save();
            return ResponseTrait::response('La descripción se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editLocation(Request $request) {
        try {
            $location = LocationEvent::where('event_id', $request->event_id)->first();
            if ($location) {
                $location->name    = trim($request->name);
                $location->address = trim($request->address);
                $location->iframe  = trim($request->iframe);
                $location->save();
                $txt = 'modificó';
            } else {
                $location = LocationEvent::create([
                    'event_id' => $request->event_id,
                    'name'     => trim($request->name),
                    'address'  => trim($request->address),
                    'iframe'   => trim($request->iframe)
                ]);
                $txt = 'guardó';
            }
            return ResponseTrait::response('La dirección se '.$txt.' correctamente.', $location);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function editContact(Request $request) {
        try {
            $event            = Event::find($request->event_id);
            $event->email     = $request->email;
            $event->phone     = $request->phone;
            $event->twitter   = $request->twitter;
            $event->facebook  = $request->facebook;
            $event->instagram = $request->instagram;
            $event->website   = $request->website;
            $event->save();
            return ResponseTrait::response('La información de contacto se guardó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
