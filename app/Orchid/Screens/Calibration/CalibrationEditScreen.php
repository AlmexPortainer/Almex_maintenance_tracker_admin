<?php

namespace App\Orchid\Screens\Calibration;

use App\Models\Calibration;
use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

/**
 * @deprecated This class is deprecated.
 */
class CalibrationEditScreen extends Screen
{
    // http://127.0.0.1:8000/admin/catalog-items/1/calibrations/1
    public $name = 'Nueva Calibración';

    public $exists = false;

    public function query(CatalogItem $catalogItem, Calibration $calibration): array
    {
        $this->exists = $calibration->exists;

        $this->name = $this->exists
            ? "Editar Calibración: {$catalogItem->codigo}"
            : "Nueva Calibración: {$catalogItem->codigo}";

        return [
            'catalogItem' => $catalogItem,
            'calibration' => $calibration,
            'calibrations' => $calibration->toArray(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Guardar')
                ->icon('check')
                ->method('save'),

            Button::make('Eliminar')
                ->icon('trash')
                ->confirm('¿Eliminar esta calibración?')
                ->method('remove')
                ->canSee($this->exists),

            Button::make('Volver al listado')
                ->icon('arrow-left')
                ->method('backToList'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                DateTimer::make('calibrations.fecha_calibracion')
                    ->title('Fecha de calibración')
                    ->format('Y-m-d')
                    ->required()
                    ->allowInput(),

                Input::make('calibrations.responsable')
                    ->title('Responsable'),

                Input::make('calibrations.reporte')
                    ->title('Reporte / Folio'),

                TextArea::make('calibrations.resultados')
                    ->title('Resultados')
                    ->rows(4),

                Switcher::make('calibrations.adecuado')
                    ->title('Adecuado para uso')
                    ->sendTrueOrFalse()
                    ->value(true),

                DateTimer::make('calibrations.fecha_proxima')
                    ->title('Próxima calibración')
                    ->format('Y-m-d')
                    ->allowInput(),

                DateTimer::make('calibrations.fecha_maxima')
                    ->title('Fecha máxima')
                    ->format('Y-m-d')
                    ->allowInput(),
            ]),
        ];
    }

    // === Métodos ===
    public function save(CatalogItem $catalogItem, Calibration $calibration, Request $request)
    {
        $validated = $request->validate([
            'calibrations.fecha_calibracion' => ['required', 'date'],
            'calibrations.responsable' => ['nullable', 'string'],
            'calibrations.reporte' => ['nullable', 'string'],
            'calibrations.resultados' => ['nullable', 'string'],
            'calibrations.adecuado' => ['boolean'],
            'calibrations.fecha_proxima' => ['nullable', 'date'],
            'calibrations.fecha_maxima' => ['nullable', 'date'],
        ]);

        $data = $validated['calibrations'] + [
            'catalog_item_id' => $catalogItem->id,
        ];

        $calibration->fill($data)->save();

        Toast::info('Calibración guardada correctamente.');

        return redirect()->route('platform.catalog_items.calibrations', $catalogItem->id);
    }

    public function remove(CatalogItem $catalogItem, Calibration $calibration)
    {
        $calibration->delete();
        Toast::info('Calibración eliminada.');

        return redirect()->route('platform.catalog_items.calibrations', $catalogItem->id);
    }

    public function backToList(CatalogItem $catalogItem)
    {
        return redirect()->route('platform.catalog_items.calibrations', $catalogItem->id);
    }
}
