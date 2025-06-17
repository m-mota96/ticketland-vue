<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Document;

class AdminController extends Controller {
    public function index() {
        $countDocs = Document::where('status', 'pending')->count();
        return Inertia::render('Admin/Dashboard', [
            'countDocs' => $countDocs
        ]);
    }
}
