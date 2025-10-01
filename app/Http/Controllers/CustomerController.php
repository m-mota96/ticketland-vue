<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Document;

class CustomerController extends Controller {
    public function usersDocuments() {
        $countDocs = Document::where('status', 'pending')->count();
        return Inertia::render('Admin/UserDocument', [
            'countDocs' => $countDocs
        ]);
    }

    public function getDocuments(Request $request) {
        $pagination = $request->pagination;
        $page       = $pagination['currentPage']; // Página actual
        $limit      = $pagination['pageSize']; // Tamaño de la página
        $offset     = ($page - 1) * $limit; // Calcular el offset
        $query      = Document::with(['user:id,name,email,phone']);
        $queryCount = Document::whereNotNull('id');

        if ($request->search['customerName']) {
            $query->whereHas('user', function($q) use($request) {
                $q->whereRaw('name LIKE "%'.$request->search['customerName'].'%"');
            });
            $queryCount->whereHas('user', function($q) use($request) {
                $q->whereRaw('name LIKE "%'.$request->search['customerName'].'%"');
            });
        }
        if ($request->search['customerEmail']) {
            $query->whereHas('user', function($q) use($request) {
                $q->whereRaw('email LIKE "%'.$request->search['customerEmail'].'%"');
            });
            $queryCount->whereHas('user', function($q) use($request) {
                $q->whereRaw('email LIKE "%'.$request->search['customerEmail'].'%"');
            });
        }
        if ($request->search['customerPhone']) {
            $query->whereHas('user', function($q) use($request) {
                $q->whereRaw('phone LIKE "%'.$request->search['customerPhone'].'%"');
            });
            $queryCount->whereHas('user', function($q) use($request) {
                $q->whereRaw('phone LIKE "%'.$request->search['customerPhone'].'%"');
            });
        }
        if ($request->search['status']) {
            $query->where('status', $request->search['status']);
            $queryCount->where('status', $request->search['status']);
        }
        if ($request->search['type']) {
            $query->where('type', $request->search['type']);
            $queryCount->where('type', $request->search['type']);
        }
        if ($request->search['status']) {
            $query->where('status', $request->search['status']);
            $queryCount->where('status', $request->search['status']);
        }

        $documents = $query->orderBy('created_at')->offset($offset)->limit($limit)->get();
        $count     = $queryCount->count(); 
        return ResponseTrait::response('', ['documents' => $documents, 'count' => $count]);
    }

    public function statusDocument(Request $request) {
        try {
            $txt              = $request->status == 'accepted' ? 'acepto' : 'rechazo';
            $document         = Document::find($request->id);
            $document->status = $request->status;
            $document->save();
            return ResponseTrait::response('El documento se '.$txt.' correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
