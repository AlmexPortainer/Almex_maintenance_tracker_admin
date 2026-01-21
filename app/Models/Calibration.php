<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * @deprecated This class is deprecated.
 */
class Calibration extends Model
{
    use AsSource, Filterable,HasFactory;

    protected $fillable = [
        'catalog_item_id',
        'fecha_calibracion',
        'responsable',
        'reporte',
        'resultados',
        'observaciones',
        'adecuado',
        'fecha_proxima',
        'fecha_maxima',
    ];

    protected $casts = [
        'fecha_calibracion' => 'date',
        'fecha_proxima' => 'date',
        'fecha_maxima' => 'date',
        'adecuado' => 'bool',
    ];

    protected $allowedSorts = [
        'fecha_calibracion', 'fecha_proxima', 'fecha_maxima', 'adecuado',
    ];

    protected $allowedFilters = [
        'responsable', 'reporte', 'adecuado',
    ];

    // App\Models\Calibration.php
    public function item()
    {
        return $this->belongsTo(CatalogItem::class, 'catalog_item_id');
    }
}
