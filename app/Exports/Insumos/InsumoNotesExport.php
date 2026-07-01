<?php

namespace App\Exports\Insumos;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InsumoNotesExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents, WithTitle, ShouldAutoSize
{
    private Collection $rows;

    public function __construct(
        private readonly Collection $notes
    ) {
        $this->rows = $this->notes->flatMap(function ($note) {
            if ($note->details_insumos->isEmpty()) {
                return collect([
                    [
                        'note' => $note,
                        'detail' => null,
                    ],
                ]);
            }

            return $note->details_insumos->map(function ($detail) use ($note) {
                return [
                    'note' => $note,
                    'detail' => $detail,
                ];
            });
        });
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Notas de insumos';
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora',
            'Administrador',
            'Correo',
            'Insumo utilizado',
            'Cantidad utilizada',
            'Stock actual',
            'Registro',
        ];
    }

    public function map($row): array
    {
        $note = $row['note'];
        $detail = $row['detail'];

        return [
            data_get($note, 'date', '-'),
            data_get($note, 'hour', '-'),
            data_get($note, 'users.name', 'Sin usuario'),
            data_get($note, 'users.email', 'Sin correo'),
            $detail ? data_get($detail, 'insumos.name', 'Insumo eliminado') : 'Sin detalle',
            $detail ? (int) data_get($detail, 'amount', 0) : 0,
            $detail ? (int) data_get($detail, 'insumos.amount', 0) : 0,
            optional(data_get($note, 'created_at'))->format('d/m/Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 14,
            'C' => 28,
            'D' => 32,
            'E' => 38,
            'F' => 18,
            'G' => 16,
            'H' => 22,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();

                $lastRow = $this->rows->count() + 1;

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:H{$lastRow}");
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EF4444'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(38);

                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F9FAFB'],
                            ],
                        ]);
                    }

                    $sheet->getStyle("F{$row}:G{$row}")->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                }
            },
        ];
    }
}
