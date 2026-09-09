<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstrumentEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instrument_id' => 'required|exists:instruments,id',
            'event_type' => 'required|in:CALIBRACION,VALIDACION,MANTENIMIENTO',
            'fecha_evento' => 'required|date',
            'responsable' => 'nullable|string|max:255',
            'reporte' => 'nullable|string|max:255',
            'resultados' => 'nullable|string',
            'adecuado' => 'boolean',
            'fecha_proxima' => 'nullable|date',
            'fecha_maxima' => 'nullable|date',
        ];
    }
}
