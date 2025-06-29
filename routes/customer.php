<?php

use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/* El middleware validate_event verifica si el evento le pertenece al usuario, si se le pasa view como parámetro quiere decir que esta entrando a una vista
y si no le pertenece lo va a redireccionar a su Dashboard. En cambio si se le pasa request quiere decir que es una petición desde el front y le devolverá 
una respuesta que no tiene permiso de modificar dicho evento */

/* NOTA: cuando se acceda a una vista que requiere el id del evento siempre se debe pasar un parámetro con el nombre event_id.
Y cuando se realice una petición para editar o agregar datos al evento de igual manera se le debe mandar un parámetro con el nombre mencionado anteriormente. */

Route::get('cliente/mis_eventos', [EventController::class, 'index'])->middleware(['auth', 'verified'])->name('cliente.mis_eventos');
Route::get('cliente/mi_perfil', [MyProfileController::class, 'index'])->middleware(['auth', 'verified'])->name('cliente.mi_perfil');

Route::prefix('cliente')->name('cliente.')->middleware(['auth', 'verified', 'role:customer', 'validate_event:view'])->group(function() {
    Route::get('evento/{event_id}', [EventController::class, 'event'])->name('evento');
    Route::get('boletos/{event_id}', [TicketController::class, 'tickets'])->name('boletos');
    Route::get('descuentos/{event_id}', [DiscountController::class, 'discounts'])->name('descuentos');
    Route::get('reservaciones/{event_id}', [ReservationController::class, 'reservations'])->name('reservaciones');
    Route::get('estadisticas/{event_id}', [StatisticController::class, 'statistics'])->name('estadisticas');
    Route::get('escaner/{event_id}', [ScannerController::class, 'scanner'])->name('escaner');
    Route::get('downloadReservations/{event_id}', [ReservationController::class, 'downloadReservations'])->name('downloadReservations');
});


Route::prefix('customer')->middleware('auth')->group(function () {
    Route::get('events', [EventController::class, 'getEvents']);
    Route::post('event', [EventController::class, 'createEvent']);
    Route::post('uploadFiles', [MyProfileController::class, 'uploadFiles']);
    Route::post('taxInformation', [MyProfileController::class, 'taxInformation']);
    Route::post('bankData', [MyProfileController::class, 'bankData']);
});

Route::prefix('customer')->middleware(['auth', 'validate_event:request'])->group(function () {
    Route::put('changeStatusEvent', [EventController::class, 'changeStatusEvent']);
    Route::put('editEvent', [EventController::class, 'editEvent']);
    Route::post('uploadImages', [EventController::class, 'uploadImages']);
    Route::delete('deleteLogo', [EventController::class, 'deleteLogo']);
    Route::put('editDescription', [EventController::class, 'editDescription']);
    Route::put('editLocation', [EventController::class, 'editLocation']);
    Route::put('editContact', [EventController::class, 'editContact']);
    Route::get('tickets', [TicketController::class, 'getTickets']);
    Route::post('ticket', [TicketController::class, 'createTicket']);
    Route::put('ticket', [TicketController::class, 'editTicket']);
    Route::put('ticketStatus', [TicketController::class, 'ticketStatus']);
    Route::delete('ticket', [TicketController::class, 'deleteTicket']);
    Route::get('discounts', [DiscountController::class, 'getDiscounts']);
    Route::post('discount', [DiscountController::class, 'createDiscount']);
    Route::put('discount', [DiscountController::class, 'editDiscount']);
    Route::put('discountStatus', [DiscountController::class, 'discountStatus']);
    Route::delete('discount', [DiscountController::class, 'deleteDiscount']);
    Route::post('reservations', [ReservationController::class, 'getReservations']);
    Route::post('resendEmail', [ReservationController::class, 'resendEmail']);
    Route::get('statistics', [StatisticController::class, 'getStatistics']);
    Route::put('editModelPayment', [EventController::class, 'editModelPayment']);
    Route::post('validateAccess', [ScannerController::class, 'validateAccess']);
    Route::get('downloadTickets', [ReservationController::class, 'downloadTickets']);
});