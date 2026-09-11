<?php

namespace App\Orchid\Concerns;

use App\Exports\TableExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;

/**
 * Añade exportación CSV / Excel a un tablero (Screen con Layout::table).
 *
 * La pantalla debe implementar:
 *  - exportFileName(): string          nombre base del archivo
 *  - exportHeadings(): array           encabezados de columna
 *  - exportRows(): array               filas (texto plano), respetando filtros activos
 *
 * Y agregar `...$this->exportButtons()` a su commandBar().
 */
trait ExportsTable
{
    abstract protected function exportFileName(): string;

    /** @return array<int, string> */
    abstract protected function exportHeadings(): array;

    /** @return array<int, array<int, mixed>> */
    abstract protected function exportRows(): array;

    /** @return array<int, Button> */
    protected function exportButtons(): array
    {
        return [
            Button::make('CSV')
                ->icon('bs.filetype-csv')
                ->method('exportCsv')
                ->rawClick()
                ->novalidate(),

            Button::make('Excel')
                ->icon('bs.file-earmark-excel')
                ->method('exportXlsx')
                ->rawClick()
                ->novalidate(),
        ];
    }

    public function exportCsv()
    {
        return $this->downloadExport(ExcelFormat::CSV, 'csv');
    }

    public function exportXlsx()
    {
        return $this->downloadExport(ExcelFormat::XLSX, 'xlsx');
    }

    private function downloadExport(string $writer, string $ext)
    {
        $name = Str::slug($this->exportFileName()).'-'.now()->format('Ymd_His').'.'.$ext;

        return Excel::download(
            new TableExport($this->exportHeadings(), $this->exportRows()),
            $name,
            $writer,
        );
    }
}
