<?php

namespace App\Orchid\Screens\Calibration;

use App\Models\Calibration;
use App\Models\CatalogItem;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

/**
 * @deprecated This class is deprecated.
 */
class CalibrationListScreen extends Screen
{
    public $name = 'Calibraciones';

    public $description = 'Histórico de calibraciones del equipo';

    private CatalogItem $catalogItem;

    public function query(CatalogItem $catalogItem): array
    {
        $this->catalogItem = $catalogItem;
        $this->name = "Calibraciones: {$catalogItem->codigo}";

        return [
            'catalogItem' => $catalogItem,
            'calibrations' => $catalogItem->calibrations()
                ->defaultSort('fecha_calibracion', 'desc')
                ->paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Atrás al Catálogo')
                ->icon('arrow-left')
                ->route('platform.catalog_items'),

            Link::make('Nuevo')
                ->icon('plus')
                ->route('platform.catalog_items.calibrations.create', $this->catalogItem->id),
        ];
    }

    public function layout(): array
    {
        return [
            new class extends Table
            {
                protected $target = 'calibrations';

                protected function columns(): array
                {
                    return [
                        TD::make('fecha_calibracion', 'Fecha Cal.')
                            ->sort(),

                        TD::make('responsable', 'Responsable')
                            ->filter()
                            ->render(fn (Calibration $c) => e($c->responsable ?: '—')),

                        TD::make('reporte', 'Reporte')
                            ->render(fn (Calibration $c) => e($c->reporte ?: '—')),

                        TD::make('adecuado', 'Adecuado')
                            ->render(fn (Calibration $c) => $c->adecuado ? 'Sí' : 'No')
                            ->sort(),

                        TD::make('fecha_proxima', 'Próxima')
                            ->sort(),

                        TD::make('fecha_maxima', 'Máxima')
                            ->sort(),

                        TD::make('Acciones')->render(function (Calibration $c) {
                            return Link::make('Editar')
                                ->icon('pencil')
                                ->route('platform.catalog_items.calibrations.edit', [$c->catalog_item_id, $c->id]);
                        }),
                    ];
                }
            },
        ];
    }
}
