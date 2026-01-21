<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstrumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:INSTRUMENTO,PLANO',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'form' => 'nullable|string|max:255',
            'Variable Unidad De Medida' => 'nullable|string|max:255',
            'equipo' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'code' => 'required|string|max:255|unique:intruments,code',
            'emt_value' => 'nullable|string|max:255',
            'emt_value_decimal' => 'nullable|numeric',
            'emt_unit' => 'nullable|string|max:255',
            'emt_symmetry' => 'nullable|boolean',
            'file_manual' => 'nullable|string|max:255',
            'types_of_criticality' => 'nullable|in:NO_CRITICO,CRITICO',
            'level_of_criticality' => 'nullable|in:BAJA,MEDIA,ALTA',
            'calibration_periodicity_days' => 'nullable|integer|min:0|max:3650',
            'validation_periodicity_days' => 'nullable|integer|min:0|max:3650',
            'maintenance_periodicity_days' => 'nullable|integer|min:0|max:3650',
            'is_operational' => 'nullable|boolean',
            'observations' => 'nullable|string',
        ];
    }
}
