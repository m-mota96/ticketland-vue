<?php

use App\Http\Controllers\GeneralEventController;
use Illuminate\Support\Facades\Route;

Route::get('evento/{url}/{ticket?}', [GeneralEventController::class, 'event'])->name('evento');

Route::get('makePayment', [GeneralEventController::class, 'makePayment']);
Route::get('verifyCodes', [GeneralEventController::class, 'verifyCodes']);
Route::get('createOrder', [GeneralEventController::class, 'createOrder']);