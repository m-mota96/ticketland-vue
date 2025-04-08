<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('cliente')->name('cliente.')->middleware(['auth', 'role:customer'])->group(function() {
    Route::get('mis_eventos', [EventController::class, 'index'])->name('mis_eventos');
    Route::get('evento/{id}', [EventController::class, 'event'])->name('evento');
});

Route::prefix('customer')->middleware('auth')->group(function () {
    Route::get('events', [EventController::class, 'getEvents']);
    Route::post('event', [EventController::class, 'createEvent']);
    Route::put('changeStatusEvent', [EventController::class, 'changeStatusEvent']);
});