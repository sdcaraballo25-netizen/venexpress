<?php

namespace App\Livewire\Concerns;

use App\Exports\SimpleArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Descarga de un reporte en Excel (.xlsx) desde un método de
 * componente Livewire (Livewire 3 soporta devolver una respuesta de
 * descarga directamente desde una acción, sin controlador aparte).
 *
 * Reemplaza a la anterior ExportsCsv: mismo propósito (un único punto
 * para el formato del archivo, en vez de repetirlo en cada
 * componente), pero ahora vuelca a .xlsx en lugar de .csv — encabezado
 * en negrita/con color y columnas autoajustadas, para que quede mejor
 * ordenado al abrirlo en Excel.
 */
trait ExportsSpreadsheet
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<array<int, scalar|null>>  $rows  Cada fila como array de valores, en el mismo orden que $headings.
     */
    protected function excelDownload(string $filename, array $headings, iterable $rows): BinaryFileResponse
    {
        return Excel::download(
            new SimpleArrayExport($headings, $rows),
            $filename,
        );
    }
}
