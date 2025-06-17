<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
