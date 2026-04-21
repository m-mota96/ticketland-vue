<?php

namespace App\Exports;

use App\Models\Code;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CodesExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithMapping, WithTitle, WithColumnFormatting {
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
            'Nombre del influencer',
            'Correo electrónico',
            'Cupón',
            'Porcentaje de descuento',
            'Boletos vendidos',
            'Monto total de descuentos',
            'Ganancia del influencer'
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 5,
            'B' => 50,            
            'C' => 50,            
            'D' => 25,
            'E' => 25,
            'F' => 25,
            'G' => 25,
            'H' => 25
        ];
    }

    public function collection() {
        return Code::with(['accesses' => function($q) {
            $q->whereHas('payment', function($q2) {
                $q2->where('status', 'payed');
            });
        }])
        ->where('event_id', $this->event_id)
        ->whereNotNull('customer_name')
        ->whereNotNull('email')
        ->get();
    }

    public function map($code): array {
        $total = 0;
        foreach ($code->accesses as $key => $a) {
            if ($a->code_discount) {
                $total = $total + ($a->price * ($a->code_discount / 100));
            }
        }

        return [
            $code->id,
            $code->customer_name,
            $code->email,
            $code->code,
            $code->discount.'%',
            sizeof($code->accesses),
            $total,
            $total * .10,
        ];
    }

    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:Z1000')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        // Estilos para la fila 1
        $sheet->getStyle('A1:H1')->applyFromArray([
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
            'G' => '"$"#,##0.00',
            'H' => '"$"#,##0.00',
        ];
    }
}
