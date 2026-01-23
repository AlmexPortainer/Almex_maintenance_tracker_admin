<?php

namespace App\Orchid\Screens\Calibration;

use App\Models\Calibration;
use App\Models\CatalogItem;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

/**
 * @deprecated This class is deprecated.
 */
class CalibrationGlobalListScreen extends Screen
{
    public $name = 'Calibraciones (Global)';

    public $description = 'Todas las calibraciones registradas en el sistema';

    public function query(): array
    {
        return [
            'calibrations' => Calibration::query()
                ->with('item')
                ->defaultSort('fecha_calibracion', 'desc')
                ->paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            // Si quieres permitir crear desde aquí, podemos abrir un modal para seleccionar el CatalogItem.
            // Para mantenerlo simple, crea desde el ítem (lista anidada).
            Link::make('Ir al Catálogo de Ítems')->icon('list')->route('platform.catalog_items'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('calibrations', [
                TD::make('item_id', 'Item')
                    ->render(function (Calibration $c) {
                        $item = $c->item;
                        if (! $item) {
                            return '—';
                        }

                        return Link::make("{$item->codigo} · {$item->equipo}")
                            ->route('platform.catalog_items.view', $item->id);
                    })
                    ->width('200px'),

                TD::make('fecha_calibracion', 'Fecha Cal.')
                    ->sort()
                    // Cambiamos el filtro de calendario por texto:
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (Calibration $c) {
                        $d = $c->fecha_calibracion;

                        return $d
                            ? sprintf('<div>%s <br><small class="text-muted">%s</small></div>',
                                e($d->format('Y-m-d')),
                                e($d->diffForHumans()))
                            : '—';
                    })
                    ->alignCenter()
                    ->width('130px'),

                TD::make('responsable', 'Responsable')
                    ->filter(TD::FILTER_TEXT),

                TD::make('reporte', 'Reporte/Folio')
                    ->defaultHidden(),

                TD::make('adecuado', 'Adecuado')
                    ->sort()
                    ->render(fn (Calibration $c) => $c->adecuado
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-danger">No</span>'
                    )
                    ->filter(TD::FILTER_TEXT)
                    ->alignCenter(),

                TD::make('fecha_proxima', 'Próxima')
                    ->sort()
                    // Texto en lugar de calendario:
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (Calibration $c) {
                        $d = $c->fecha_proxima;
                        if (! $d) {
                            return '—';
                        }
                        // Etiqueta de estado simple
                        $days = now()->diffInDays($d, false); // negativo si ya pasó
                        $badge = $days < 0
                            ? '<span class="badge bg-danger">Vencida</span>'
                            : ($days <= 7
                                ? '<span class="badge bg-warning">Próxima</span>'
                                : '<span class="badge bg-success">OK</span>');

                        return sprintf(
                            '<div>%s <small class="text-muted">%s</small><div>%s</div></div>',
                            e($d->format('Y-m-d')),
                            e($d->diffForHumans()),
                            $badge
                        );
                    })
                    ->width('130px')
                    ->alignCenter(),

                TD::make('fecha_maxima', 'Máxima')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (Calibration $c) {
                        $d = $c->fecha_maxima;
                        if (! $d) {
                            return '—';
                        }
                        $overdue = now()->greaterThan($d);

                        return sprintf(
                            '<div>%s <small class="text-muted">%s</small><div>%s</div></div>',
                            e($d->format('Y-m-d')),
                            e($d->diffForHumans()),
                            $overdue
                                ? '<span class="badge bg-danger">Fuera de rango</span>'
                                : '<span class="badge bg-success">Dentro de rango</span>'
                        );
                    })
                    ->width('130px')
                    ->alignCenter(),

                TD::make('Acciones')
                    ->alignRight()
                    ->render(function (Calibration $c) {
                        return Link::make('Editar')
                            ->icon('pencil')
                            ->route('platform.catalog_items.calibrations.edit', [$c->catalog_item_id, $c->id]);
                    }),
            ]),
        ];

    }
}
