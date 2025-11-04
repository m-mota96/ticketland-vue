<?php

use App\Http\Controllers\Api\CronjobController;
use App\Http\Controllers\Api\WebHookController;
use Illuminate\Support\Facades\Route;

Route::post('/validatePayment', [WebHookController::class, 'validatePayment']);
Route::get('/ticketsExpired', [CronjobController::class, 'ticketsExpired']);
Route::get('/disableEvents', [CronjobController::class, 'disableEvents']);