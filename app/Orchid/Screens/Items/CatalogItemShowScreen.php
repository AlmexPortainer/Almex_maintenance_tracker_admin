<?php

namespace App\Orchid\Screens\Items;

use App\Models\Calibration;
use App\Models\CatalogItem;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItemShowScreen extends Screen
{
    public $name = 'Detalle de Ítem';

    public $description = 'Ficha técnica y últimos mantenimientos';

    protected CatalogItem $catalogItem;

    public function query(CatalogItem $catalogItem): array
    {
        $this->catalogItem = $catalogItem;
        $this->name = "Detalle: {$catalogItem->codigo}";

        return [
            'catalogItem' => $catalogItem,
            'calibrations' => $catalogItem->calibrations()
                ->orderByDesc('fecha_calibracion')
                ->limit(10)
                ->get(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Editar ítem')
                ->icon('pencil')
                ->route('platform.catalog_items.edit', $this->catalogItem->id),

            Link::make('Nueva calibración')
                ->icon('plus')
                ->route('platform.catalog_items.calibrations.create', $this->catalogItem->id),

            Link::make('Ver todas las calibraciones')
                ->icon('wrench')
                ->route('platform.catalog_items.calibrations', $this->catalogItem->id),

            Link::make('Volver al listado')
                ->icon('arrow-left')
                ->route('platform.catalog_items'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::legend('catalogItem', [
                Sight::make('codigo', 'Código'),
                Sight::make('tipo_item', 'Tipo de Ítem'),
                Sight::make('equipo', 'Equipo'),
                Sight::make('marca', 'Marca'),
                Sight::make('modelo', 'Modelo'),
                Sight::make('requiere_calibracion', 'Requiere Calibración')
                    ->render(fn ($i) => $i->requiere_calibracion ? 'Sí' : 'No'),
                Sight::make('tipo', 'Criticidad'),
                Sight::make('estado', 'Estado'),

                Sight::make('emt_valor', 'EMT - Valor'),
                Sight::make('emt_unidad', 'EMT - Unidad'),
                Sight::make('emt_simetrico', 'EMT Simétrico')
                    ->render(fn ($i) => is_null($i->emt_simetrico) ? '—' : ($i->emt_simetrico ? 'Sí' : 'No')),

                Sight::make('ubicacion', 'Ubicación'),
                Sight::make('forma', 'Forma'),

                Sight::make('ult_fecha_ultima', 'Última Cal.'),
                Sight::make('ult_fecha_proxima', 'Próxima Cal.'),
                Sight::make('ult_fecha_maxima', 'Fecha Máxima'),
                Sight::make('ult_dias_retraso', 'Días de Retraso'),
                Sight::make('ult_calibro', 'Calibró'),
                Sight::make('ult_reporte', 'Reporte/Folio'),
                Sight::make('ult_adecuado_uso', 'Adecuado p/uso')
                    ->render(fn ($i) => is_null($i->ult_adecuado_uso) ? '—' : ($i->ult_adecuado_uso ? 'Sí' : 'No')),
                Sight::make('ult_resultados', 'Resultados'),
                Sight::make('ult_observaciones', 'Observaciones'),
            ]),
            Layout::table('calibrations', [
                TD::make('fecha_calibracion', 'Fecha Cal.')
                    ->sort()
                    ->render(function (Calibration $c) {
                        if (! $c->fecha_calibracion) {
                            return '<span class="text-muted">—</span>';
                        }
                        $d = $c->fecha_calibracion;

                        return sprintf(
                            '<div class="text-center">
                        %s<br><small class="text-muted">%s</small>
                    </div>',
                            e($d->format('Y-m-d')),
                            e($d->diffForHumans())
                        );
                    })
                    ->alignCenter(),

                TD::make('responsable', 'Responsable')
                    ->render(fn (Calibration $c) => e($c->responsable ?: '—')),

                TD::make('reporte', 'Reporte/Folio')
                    ->render(fn (Calibration $c) => e($c->reporte ?: '—')),

                TD::make('adecuado', 'Adecuado')
                    ->render(fn (Calibration $c) => $c->adecuado
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-danger">No</span>'
                    )
                    ->alignCenter(),

                TD::make('fecha_proxima', 'Próxima')
                    ->render(function (Calibration $c) {
                        if (! $c->fecha_proxima) {
                            return '<span class="text-muted">—</span>';
                        }
                        $d = $c->fecha_proxima;
                        $days = now()->diffInDays($d, false);
                        $badge = $days < 0
                            ? '<span class="badge bg-danger">Vencida</span>'
                            : ($days <= 7
                                ? '<span class="badge bg-warning text-dark">Próxima</span>'
                                : '<span class="badge bg-success">OK</span>');

                        return sprintf(
                            '<div class="text-center">
                        %s<br><small class="text-muted">%s</small><div>%s</div>
                    </div>',
                            e($d->format('Y-m-d')),
                            e($d->diffForHumans()),
                            $badge
                        );
                    })
                    ->alignCenter(),

                TD::make('fecha_maxima', 'Máxima')
                    ->render(function (Calibration $c) {
                        if (! $c->fecha_maxima) {
                            return '<span class="text-muted">—</span>';
                        }
                        $d = $c->fecha_maxima;
                        $overdue = now()->greaterThan($d);

                        return sprintf(
                            '<div class="text-center">
                                        %s<br><small class="text-muted">%s</small><div>%s</div>
                                    </div>',
                            e($d->format('Y-m-d')),
                            e($d->diffForHumans()),
                            $overdue
                                ? '<span class="badge bg-danger">Fuera de rango</span>'
                                : '<span class="badge bg-success">Dentro de rango</span>'
                        );
                    })
                    ->alignCenter(),

                TD::make('Acciones')
                    ->alignRight()
                    ->render(function (Calibration $c) {
                        return Link::make('Editar')
                            ->icon('pencil')
                            ->route('platform.catalog_items.calibrations.edit', [$c->catalog_item_id, $c->id])
                            ->class('btn btn-sm btn-outline-primary');
                    }),
            ]),
        ];
    }
}
