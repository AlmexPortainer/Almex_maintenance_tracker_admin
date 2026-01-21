<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @deprecated This class is deprecated.
 */
class CalibrationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'catalog_item_id' => $this->catalog_item_id,
            'fecha_calibracion' => optional($this->fecha_calibracion)->toDateString(),
            'responsable' => $this->responsable,
            'reporte' => $this->reporte,
            'resultados' => $this->resultados,
            'observaciones' => $this->observaciones,
            'adecuado' => $this->adecuado,
            'fecha_proxima' => optional($this->fecha_proxima)->toDateString(),
            'fecha_maxima' => optional($this->fecha_maxima)->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
