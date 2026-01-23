<?php

namespace App\Orchid\Screens\Items;

use App\Models\CatalogItem;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItemListScreen extends Screen
{
    public $name = 'Catálogo de Instrumentos';

    public $description = 'Gestión de instrumentos/planos';

    public function query(): array
    {
        return [
            'items' => CatalogItem::paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Nuevo')
                ->icon('plus')
                ->route('platform.catalog_items.create'),
        ];
    }

    public function layout(): array
    {
        return [
            new class extends Table
            {
                protected $target = 'items';

                protected function columns(): array
                {
                    return [
                        TD::make('codigo', 'Código')
                            ->sort()
                            ->filter()
                            ->render(function (CatalogItem $i) {
                                return Link::make($i->codigo)
                                    ->icon('eye')
                                    ->route('platform.catalog_items.view', $i->id);
                            })
                            ->width('180px'),

                        TD::make('equipo', 'Equipo')
                            ->sort()
                            ->filter()
                            ->render(fn (CatalogItem $i) => e($i->equipo)),

                        TD::make('marca', 'Marca')
                            ->filter()
                            ->render(fn (CatalogItem $i) => $i->marca ?: '—'),

                        TD::make('estado', 'Estado')
                            ->sort()
                            ->filter()
                            ->render(function (CatalogItem $i) {
                                return match ($i->estado) {
                                    'ALTA' => '<span class="badge bg-danger">Alta</span>',
                                    'MEDIA' => '<span class="badge bg-warning text-dark">Media</span>',
                                    'BAJA' => '<span class="badge bg-success">Baja</span>',
                                    default => '<span class="badge bg-secondary">—</span>',
                                };
                            })
                            ->alignCenter(),

                        TD::make('tipo_item', 'Tipo de Ítem')
                            ->filter()
                            ->render(fn (CatalogItem $i) => $i->tipo_item === 'INSTRUMENTO'
                                ? '<span class="badge bg-warning text-dark">Instrumento</span>'
                                : '<span class="badge bg-primary">Plano</span>')
                            ->alignCenter(),

                        TD::make('tipo', 'Criticidad')
                            ->filter()
                            ->render(function (CatalogItem $i) {
                                return $i->tipo === 'CRITICO'
                                    ? '<span class="badge bg-danger">Crítico</span>'
                                    : '<span class="badge bg-success">No crítico</span>';
                            })
                            ->alignCenter(),

                        TD::make('ult_fecha_proxima', 'Próxima Cal.')
                            ->sort()
                            ->render(function (CatalogItem $i) {
                                if (! $i->ult_fecha_proxima) {
                                    return '<span class="text-muted">—</span>';
                                }

                                $d = $i->ult_fecha_proxima;
                                $days = now()->diffInDays($d, false);
                                $badge = $days < 0
                                    ? '<span class="badge bg-danger">Vencida</span>'
                                    : ($days <= 7
                                        ? '<span class="badge bg-warning text-dark">Próxima</span>'
                                        : '<span class="badge bg-success">OK</span>');

                                return sprintf(
                                    '<div class="text-center">
                                                %s<br>
                                                <small class="text-muted">%s</small><br>
                                                %s
                                            </div>',
                                    e($d->format('Y-m-d')),
                                    e($d->diffForHumans()),
                                    $badge
                                );
                            })
                            ->width('130px')
                            ->alignCenter(),

                        TD::make('Acciones')
                            ->alignRight()
                            ->render(function (CatalogItem $i) {
                                return
                                    Link::make('Editar')
                                        ->icon('pencil')
                                        ->route('platform.catalog_items.edit', $i->id)
                                        ->class('btn btn-sm btn-outline-primary').
                                    '</div>';
                            }),
                    ];

                }
            },
        ];
    }
}
