<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @deprecated This class is deprecated, use class UpdateIntrumentRequest instead.
 */
class UpdateCatalogItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('catalogItem')?->id ?? $this->route('id');

        return [
            'codigo' => ['sometimes', 'string', 'max:50', "unique:catalog_items,codigo,{$id}"],
            'tipo_item' => ['sometimes', 'in:INSTRUMENTO,PLANO'],
            'equipo' => ['sometimes', 'string', 'max:100'],
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
