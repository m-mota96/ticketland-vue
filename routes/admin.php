<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::prefix('administrador')->name('administrador.')->middleware(['auth', 'verified', 'role:admin'])->group(function() {
    Route::get('inicio', [AdminController::class, 'index'])->name('inicio');
    Route::get('usuarios-documentos', [CustomerController::class, 'usersDocuments'])->name('usuarios-documentos');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function() {
    Route::get('getDocuments', [CustomerController::class, 'getDocuments']);
    Route::put('statusDocument', [CustomerController::class, 'statusDocument']);
});