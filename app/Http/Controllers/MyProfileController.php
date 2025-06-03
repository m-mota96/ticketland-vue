<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\BankData;
use App\Models\Document;
use App\Models\TaxData;

class MyProfileController extends Controller {
    public function index() {
        $user = [
            'name'   => auth()->user()->name,
            'email'  => auth()->user()->email,
            'phone'  => auth()->user()->phone,
            'status' => auth()->user()->contract == 'active' ? true : false
        ];

        $tax_data  = TaxData::where('user_id', auth()->user()->id)->first();
        $bank_data = BankData::where('user_id', auth()->user()->id)->first();

        return Inertia::render('Customer/Profile', [
            'user'            => $user,
            'tax_information' => $tax_data,
            'bank_data'       => $bank_data,
            'documents'       => self::getFiles()
        ]);
    }

    public function taxInformation(Request $request) {
        try {
            $tax_data = TaxData::create([
                'user_id'              => auth()->user()->id,
                'legal_representative' => trim($request->legal_representative),
                'business_name'        => trim($request->business_name),
                'rfc'                  => trim($request->rfc),
                'address'              => trim($request->address),
                'external_number'      => trim($request->external_number),
                'internal_number'      => trim($request->internal_number),
                'colony'               => trim($request->colony),
                'postal_code'          => trim($request->postal_code),
                'city'                 => trim($request->city),
                'state'                => trim($request->state),
            ]);
            return ResponseTrait::response('La información fiscal se guardó correctamente.', $tax_data);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function bankData(Request $request) {
        try {
            // $bank_data = BankData::create([
            //     'user_id'         => auth()->user()->id,
            //     'name_propietary' => trim($request->name_propietary),

            // ]);
        } catch (\Throwable $th) {
            return ResponseTrait::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }

    public function uploadFiles(Request $request) {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // tamaño en kilobytes (1024 KB = 1 MB)
            ]);
            $file            = $request->file;
            $extension       = $file->getClientOriginalExtension();
            $fileName        = uniqid().'.'.$extension;
            $destinationPath = public_path('customers');
            
            $document = Document::where('type', $request->type)->where('user_id', auth()->user()->id)->first();

            DB::beginTransaction();
            if ($document) {
                if (file_exists('customers/' . $document->document)) {
                    unlink('customers/' . $document->document);
                }
                $document->document = $fileName;
                $document->status   = 'pending';
                $document->save();
            } else {
                Document::create([
                    'user_id'  => auth()->user()->id,
                    'document' => $fileName,
                    'type'     => $request->type,
                    'status'   => 'pending'
                ]);
            }

            // Crear carpeta si no existe
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Mover el archivo
            $file->move($destinationPath, $fileName);

            // URL pública del archivo
            $url = asset('customers/' . $fileName);
            DB::commit();
            return ResponseTrait::response('Archivo cargado correctamente.', ['type' => $request->type, 'documents' => self::getFiles()]);
        } catch (\Throwable $th) {
            return ResponseTrait::response('El archivo debe estar en formato<br>JPG, PNG o PDF y pesar menos de 5MB.', $th->getMessage(), true, 422);
        }
    }

    private function getFiles() {
        $constitutive   = Document::where('user_id', auth()->user()->id)->where('type', 'constitutive')->first();
        $address        = Document::where('user_id', auth()->user()->id)->where('type', 'address')->first();
        $bank_receipt   = Document::where('user_id', auth()->user()->id)->where('type', 'bank_receipt')->first();
        $identification = Document::where('user_id', auth()->user()->id)->where('type', 'identification')->first();
        return [
            'constitutive'   => !$constitutive ? ['status' => null] : ['status' => $constitutive->status, 'document' => $constitutive->document],
            'address'        => !$address ? ['status' => null] : ['status' => $address->status, 'document' => $address->document],
            'bank_receipt'   => !$bank_receipt ? ['status' => null] : ['status' => $bank_receipt->status, 'document' => $bank_receipt->document],
            'identification' => !$identification ? ['status' => null] : ['status' => $identification->status, 'document' => $identification->document],
        ];
    }
}
