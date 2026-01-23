<?php

namespace App\Orchid\Screens\Items;

use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItemEditScreen extends Screen
{
    public $name = 'Editar Ítem';

    public $exists = false;

    public function query(CatalogItem $catalogItem): array
    {
        $this->exists = $catalogItem->exists;
        if ($this->exists) {
            $this->name = "Editar: {$catalogItem->codigo}";
        } else {
            $this->name = 'Nuevo Ítem';
        }

        return [
            'catalog_items' => $catalogItem->toArray(),
            'catalogItem' => $catalogItem,
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Guardar')->icon('check')->method('save')->canSee(true),
            Button::make('Eliminar')->icon('trash')->method('remove')
                ->confirm('¿Eliminar definitivamente este ítem?')
                ->canSee($this->exists),

        ];
    }

    public function layout(): array
    {
        return [
            // Puedes reemplazar por Layout::block(...) si quieres títulos por sección
            Layout::rows([
                Input::make('catalog_items.codigo')->title('Código')->required(),
                Select::make('catalog_items.tipo_item')->title('Tipo de Ítem')->options([
                    'INSTRUMENTO' => 'Instrumento',
                    'PLANO' => 'Plano',
                ])->required(),
                Input::make('catalog_items.equipo')->title('Equipo')->required(),
                Input::make('catalog_items.marca')->title('Marca'),
                Input::make('catalog_items.modelo')->title('Modelo'),
                Switcher::make('catalog_items.requiere_calibracion')->title('Requiere Calibración')->sendTrueOrFalse()->value(true),
                Select::make('catalog_items.tipo')->title('Criticidad')->options([
                    'NO_CRITICO' => 'No crítico',
                    'CRITICO' => 'Crítico',
                ])->value('NO_CRITICO'),
                Select::make('catalog_items.estado')->title('Estado')->options([
                    'BAJA' => 'Baja', 'MEDIA' => 'Media', 'ALTA' => 'Alta',
                ])->value('BAJA'),
                Input::make('catalog_items.emt_valor')->title('EMT - Valor')->type('number')->step('0.0001'),
                Input::make('catalog_items.emt_unidad')->title('EMT - Unidad'),
                Switcher::make('catalog_items.emt_simetrico')->title('EMT Simétrico')->sendTrueOrFalse(),
                Input::make('catalog_items.ubicacion')->title('Ubicación'),
                Input::make('catalog_items.forma')->title('Forma'),
            ]),
            Layout::rows([
                DateTimer::make('catalog_items.ult_fecha_ultima')->title('Última Calibración')->format('Y-m-d')->allowInput(),
                DateTimer::make('catalog_items.ult_fecha_proxima')->title('Próxima Calibración')->format('Y-m-d')->allowInput(),
                DateTimer::make('catalog_items.ult_fecha_maxima')->title('Fecha Máxima')->format('Y-m-d')->allowInput(),
                Input::make('catalog_items.ult_dias_retraso')->title('Días de Retraso')->type('number')->min(0),
                Input::make('catalog_items.ult_calibro')->title('Calibró'),
                Input::make('catalog_items.ult_reporte')->title('Reporte/Folio'),
                TextArea::make('catalog_items.ult_resultados')->title('Resultados')->rows(4),
                Switcher::make('catalog_items.ult_adecuado_uso')->title('Adecuado para uso')->sendTrueOrFalse(),
                TextArea::make('catalog_items.ult_observaciones')->title('Observaciones')->rows(3),
            ]),
        ];
    }

    // === Métodos de acción ===
    public function save(CatalogItem $catalogItem, Request $request)
    {
        $data = $request->validate([
            'catalog_items.codigo' => ['required', 'string', 'unique:catalog_items,codigo,'.($catalogItem->id ?? 'NULL')],
            'catalog_items.tipo_item' => ['required', 'in:INSTRUMENTO,PLANO'],
            'catalog_items.equipo' => ['required', 'string'],
            'catalog_items.marca' => ['nullable', 'string'],
            'catalog_items.modelo' => ['nullable', 'string'],
            'catalog_items.requiere_calibracion' => ['boolean'],
            'catalog_items.tipo' => ['required', 'in:NO_CRITICO,CRITICO'],
            'catalog_items.estado' => ['required', 'in:BAJA,MEDIA,ALTA'],
            'catalog_items.emt_valor' => ['nullable', 'numeric'],
            'catalog_items.emt_unidad' => ['nullable', 'string'],
            'catalog_items.emt_simetrico' => ['nullable', 'boolean'],
            'catalog_items.ubicacion' => ['nullable', 'string'],
            'catalog_items.forma' => ['nullable', 'string'],
            'catalog_items.ult_fecha_ultima' => ['nullable', 'date'],
            'catalog_items.ult_fecha_proxima' => ['nullable', 'date'],
            'catalog_items.ult_fecha_maxima' => ['nullable', 'date'],
            'catalog_items.ult_dias_retraso' => ['nullable', 'integer'],
            'catalog_items.ult_calibro' => ['nullable', 'string'],
            'catalog_items.ult_reporte' => ['nullable', 'string'],
            'catalog_items.ult_resultados' => ['nullable', 'string'],
            'catalog_items.ult_adecuado_uso' => ['nullable', 'boolean'],
            'catalog_items.ult_observaciones' => ['nullable', 'string'],
        ]);

        $catalogItem->fill($data['catalog_items'])->save();

        Toast::info('Ítem guardado correctamente.');

        return redirect()->route('platform.catalog_items');
    }

    public function remove(CatalogItem $catalogItem)
    {
        $catalogItem->delete();
        Toast::info('Ítem eliminado.');

        return redirect()->route('platform.catalog_items');
    }
}
