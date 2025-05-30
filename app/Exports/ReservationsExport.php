<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class ReservationsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles {
    public $event_id;

    public function __construct($event_id) {
        $this->event_id = $event_id;
    }

    public function headings(): array {
        return [
            '#',
            'Nombre',
            'Correo',
            'Teléfono',
            'Código de descuento',
            'Subtotal',
            'Descuento',
            'Total',
            'Método de pago',
            'Estatus',
            'Fecha de compra'
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 5,
            'B' => 35,            
            'C' => 45,            
            'D' => 25,
            'E' => 25,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 20,
            'J' => 20,
            'K' => 20
        ];
    }

    public function collection() {
        return Payment::where('event_id', $this->event_id)
        ->select(
            'id',
            'name',
            'email',
            'phone',
            DB::raw('IF(code IS NULL, "N/A", code) code'),
            'amount',
            DB::raw('CONCAT(discount, "%") discount'),
            DB::raw('amount - ROUND(amount * (discount / 100)) total'),
            DB::raw('CASE 
                WHEN type = "card" THEN "Tarjeta" 
                WHEN type = "oxxo" THEN "Efectivo" 
            END type'),
            DB::raw('CASE 
                WHEN status = "expired" THEN "Expirado" 
                WHEN status = "payed" THEN "Pagado" 
                WHEN status = "pending" THEN "Pendiente" 
            END status'),
            DB::raw('DATE_FORMAT(created_at, "%d/%m/%Y %H:%i")')
        )
        ->orderBy('id', 'DESC')
        ->get();
        // return Payment::all();
    }

    public function styles(Worksheet $sheet) {
        // Estilos para la fila 1 (A1:H1)
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '9BBB59' // Color verde (puedes cambiarlo)
                ],
            ],
        ]);

        return [];
    }
}
