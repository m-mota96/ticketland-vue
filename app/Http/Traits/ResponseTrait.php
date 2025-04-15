<?php

namespace App\Http\Traits;

trait ResponseTRait {
    public static function response($msj, $data = null, $error = false, $code = 200) {
        return response()->json([
            'error' => $error,
            'msj'   => $msj,
            'data'  => $data
        ], $code);
    }
}