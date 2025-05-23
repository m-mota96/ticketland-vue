<?php

use App\Http\Controllers\Api\WebHookController;
use Illuminate\Support\Facades\Route;

Route::post('/validatePayment', [WebHookController::class, 'validatePayment']);