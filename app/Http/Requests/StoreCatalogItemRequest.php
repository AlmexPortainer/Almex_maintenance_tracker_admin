<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @deprecated This class is deprecated, use class StoreIntrumentRequest instead.
 */
class StoreCatalogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:50', 'unique:catalog_items,codigo'],
            'tipo_item' => ['required', 'in:INSTRUMENTO,PLANO'],
            'equipo' => ['required', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'requiere_calibracion' => ['boolean'],
            'tipo' => ['in:NO_CRITICO,CRITICO'],
            'estado' => ['in:BAJA,MEDIA,ALTA'],
            'emt_valor' => ['nullable', 'numeric'],
            'emt_unidad' => ['nullable', 'string', 'max:20'],
            'emt_simetrico' => ['nullable', 'boolean'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'forma' => ['nullable', 'string', 'max:120'],
        ];
    }
}
