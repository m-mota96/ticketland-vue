<?php

use App\Http\Controllers\GeneralEventController;
use Illuminate\Support\Facades\Route;

Route::get('evento/{url}', [GeneralEventController::class, 'event'])->name('evento');