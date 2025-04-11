<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateEvent;

/* El middleware ValidateEvent verifica si el evento le pertenece al usuario, si se le pasa view como parámetro quiere decir que esta entrando a una vista
y si no le pertenece lo va a redireccionar a su Dashboard. En cambio si se le pasa request quiere decir que es una petición desde el front y le devolverá 
una respuesta que no tiene permiso de modificar dicho evento */

/* NOTA: cuando se acceda a una vista que requiere el id del evento siempre se debe pasar un parámetro con el nombre id o event_id.
Y cuando se realice una petición para editar o agregar datos al evento de igual manera se le debe mandar parámetro con uno de esos 2 nombres. */

Route::get('cliente/mis_eventos', [EventController::class, 'index'])->middleware('auth')->name('cliente.mis_eventos');

Route::prefix('cliente')->name('cliente.')->middleware(['auth', 'role:customer', ValidateEvent::class.':view'])->group(function() {
    Route::get('evento/{id}', [EventController::class, 'event'])->name('evento');
});


Route::prefix('customer')->middleware('auth')->group(function () {
    Route::get('events', [EventController::class, 'getEvents'])->middleware('auth');
    Route::post('event', [EventController::class, 'createEvent']);
});

Route::prefix('customer')->middleware(['auth', ValidateEvent::class.':request'])->group(function () {
    Route::put('changeStatusEvent', [EventController::class, 'changeStatusEvent']);
    Route::put('editEvent', [EventController::class, 'editEvent']);
    Route::post('uploadImages', [EventController::class, 'uploadImages']);
});