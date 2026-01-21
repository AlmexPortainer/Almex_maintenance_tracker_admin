<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'tipo_item' => $this->tipo_item,
            'equipo' => $this->equipo,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'requiere_calibracion' => $this->requiere_calibracion,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'emt' => [
                'valor' => $this->emt_valor,
                'unidad' => $this->emt_unidad,
                'simetrico' => $this->emt_simetrico,
            ],
            'ubicacion' => $this->ubicacion,
            'forma' => $this->forma,
            'ultima_calibracion' => [
                'fecha_ultima' => optional($this->ult_fecha_ultima)->toDateString(),
                'fecha_proxima' => optional($this->ult_fecha_proxima)->toDateString(),
                'fecha_maxima' => optional($this->ult_fecha_maxima)->toDateString(),
                'dias_retraso' => $this->ult_dias_retraso,
                'calibro' => $this->ult_calibro,
                'reporte' => $this->ult_reporte,
                'resultados' => $this->ult_resultados,
                'adecuado_uso' => $this->ult_adecuado_uso,
                'observaciones' => $this->ult_observaciones,
            ],
        ];
    }
}
