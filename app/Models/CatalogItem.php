<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItem extends Model
{
    use AsSource, Filterable, HasFactory;

    protected $table = 'catalog_items';

    protected $fillable = [
        'codigo', 'tipo_item', 'equipo', 'marca', 'modelo', 'requiere_calibracion',
        'tipo', 'estado', 'emt_valor', 'emt_unidad', 'emt_simetrico', 'ubicacion', 'forma',
        'ult_fecha_ultima', 'ult_fecha_proxima', 'ult_fecha_maxima', 'ult_dias_retraso',
        'ult_calibro', 'ult_reporte', 'ult_resultados', 'ult_adecuado_uso', 'ult_observaciones',
    ];

    protected $casts = [
        'requiere_calibracion' => 'bool',
        'emt_valor' => 'decimal:4',
        'emt_simetrico' => 'bool',
        'ult_fecha_ultima' => 'date',
        'ult_fecha_proxima' => 'date',
        'ult_fecha_maxima' => 'date',
        'ult_adecuado_uso' => 'bool',
    ];

    protected $allowedSorts = ['codigo', 'equipo', 'marca', 'estado', 'tipo_item', 'tipo', 'ult_fecha_proxima'];

    protected $allowedFilters = ['codigo', 'equipo', 'marca', 'modelo', 'estado', 'tipo_item', 'tipo', 'ubicacion'];

    // app/Models/CatalogItem.php
    public function calibrations()
    {
        return $this->hasMany(Calibration::class);
    }
}
