<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @deprecated This class is deprecated, use class StoreIntrumentEventRequest instead.
 */
class StoreCalibrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_calibracion' => ['required', 'date'],
            'responsable' => ['nullable', 'string', 'max:120'],
            'reporte' => ['nullable', 'string', 'max:120'],
            'resultados' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'adecuado' => ['boolean'],
            'fecha_proxima' => ['nullable', 'date'],
            'fecha_maxima' => ['nullable', 'date', 'after_or_equal:fecha_calibracion'],
        ];
    }
}
