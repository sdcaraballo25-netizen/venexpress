<?php

namespace App\Exports;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel genérico para los reportes del sistema: recibe encabezados y
 * filas ya calculadas (mismos que antes armaban el CSV a mano) y los
 * vuelca en un .xlsx con encabezado en negrita, relleno de color y
 * columnas autoajustadas — "mejor ordenado" que un CSV plano.
 *
 * Un único punto para el estilo, en vez de repetirlo en cada
 * componente que exporte algo (mismo espíritu que tenía
 * ExportsCsv/csvDownload antes de este cambio).
 */
class SimpleArrayExport implements FromGenerator, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<array<int, scalar|null>>  $rows
     */
    public function __construct(
        private readonly array $headings,
        private readonly iterable $rows,
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function generator(): Generator
    {
        foreach ($this->rows as $row) {
            yield $row;
        }
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        $sheet->getStyle("A1:{$lastColumn}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E3A8A');

        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->getColor()->setRGB('FFFFFF');

        $sheet->freezePane('A2');

        return [];
    }
}
