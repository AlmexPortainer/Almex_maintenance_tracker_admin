<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Export genérico de tableros: recibe encabezados y filas ya resueltas (texto plano).
 * Sirve para CSV y XLSX (el writer lo decide quien invoca Excel::download()).
 */
class TableExport implements FromArray, WithCustomCsvSettings, WithHeadings
{
    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function __construct(
        private array $headings,
        private array $rows,
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /** BOM UTF-8 para que Excel abra los acentos correctamente en CSV. */
    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }
}
