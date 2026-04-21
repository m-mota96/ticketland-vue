<?php

namespace App\Exports;

use App\Models\Access;
use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReservationsExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle, WithColumnFormatting {
    public $event_id;

    public function __construct($event_id) {
        $this->event_id = $event_id;
    }

    public function title(): string {
        return 'Hoja 1';
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
            'I' => 45,
            'J' => 20,
            'K' => 20
        ];
    }

    public function collection() {
        return Payment::with(['paymentMethod:id,name'])->where('event_id', $this->event_id)
        ->select(
            '*',
            DB::raw('CASE 
                WHEN status = "expired" THEN "Expirado" 
                WHEN status = "payed" THEN "Pagado" 
                WHEN status = "pending" THEN "Pendiente" 
            END status'),
            DB::raw('DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") AS date')
        )
        ->addSelect(['total' => Access::selectRaw('SUM(price)')
            ->whereColumn('payment_id', 'payments.id')
            ->groupBy('payment_id')
        ])
        ->orderBy('id', 'DESC')
        ->get();
        // return Payment::all();
    }

    public function map($reservation): array {
        return [
            $reservation->id,
            $reservation->name,
            $reservation->email,
            "'".$reservation->phone,
            $reservation->code ?? 'N/A',
            $reservation->total,
            $reservation->discount.'%',
            $reservation->amount,
            $reservation->paymentMethod->name,
            $reservation->status,
            $reservation->date
        ];
    }

    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:Z1000')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        // Estilos para la fila 1
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '9BBB59' // Color verde
                ],
            ],
        ]);

        return [];
    }

    public function columnFormats(): array {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
            'F' => '"$"#,##0.00',
            'H' => '"$"#,##0.00',
        ];
    }
}
