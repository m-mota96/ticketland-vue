<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller {
    public function index() {
        switch (auth()->user()->getRoleNames()[0]) {
            case 'customer':
                return redirect(route('cliente.mis_eventos'));
            break;
            case 'admin':
                return redirect(route('administrador.inicio'));
            break;
        }
    }

    public function home() {
        return Inertia::render('Home');
    }
}
