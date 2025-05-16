<?php

namespace App\Http\Traits;

trait DateFormatTrait {
    public static function parseDate($date, $separator = '/', $typeMonth = 'monthsNum') {
        $months      = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $monthsAbrev = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthsNum   = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        return substr($date, 8, 2).$separator.$$typeMonth[intval(substr($date, 5, 2)) - 1].$separator.substr($date, 0, 4);
    }
}