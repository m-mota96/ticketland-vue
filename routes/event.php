<?php

use App\Http\Controllers\GeneralEventController;
use Illuminate\Support\Facades\Route;

Route::get('evento/{url}/{ticket?}', [GeneralEventController::class, 'event'])->name('evento');

Route::post('makePayment', [GeneralEventController::class, 'makePayment']);
Route::post('verifyCodes', [GeneralEventController::class, 'verifyCodes']);
Route::post('createOrder', [GeneralEventController::class, 'createOrder']);
Route::post('captureOrder', [GeneralEventController::class, 'captureOrder']);