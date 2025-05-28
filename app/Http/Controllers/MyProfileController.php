<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;

class MyProfileController extends Controller {
    public function index() {
        $user = [
            'name'   => auth()->user()->name,
            'email'  => auth()->user()->email,
            'phone'  => auth()->user()->phone,
            'status' => auth()->user()->contract == 'active' ? true : false
        ];

        return Inertia::render('Customer/Profile', [
            'user'   => $user
        ]);
    }

    public function uploadFiles(Request $request) {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // tamaño en kilobytes (1024 KB = 1 MB)
            ]);
            
        } catch (\Throwable $th) {
            return ResponseTrait::response('El archivo debe estar en formato<br>JPG, PNG o PDF y pesar menos de 5MB.', $th->getMessage(), true, 422);
        }
    }
}
