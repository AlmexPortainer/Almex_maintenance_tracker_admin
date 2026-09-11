<?php

namespace App\Orchid\Screens\Instruments;

use App\Models\Instrument;
use App\Orchid\Concerns\ExportsTable;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstrumentFueraServicioListScreen extends Screen
{
    use ExportsTable;

    public $name = 'Instrumentos Fuera de Servicio';

    public $description = 'Instrumentos con estado Fuera de Servicio. Fuera del catálogo principal, disponibles para auditoría e histórico.';

    public function query(): iterable
    {
        return [
            'instruments' => Instrument::status(Instrument::ESTADO_FUERA_SERVICIO)->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Volver al catálogo')
                ->icon('bs.arrow-left')
                ->route('platform.instruments.list'),

            ...$this->exportButtons(),
        ];
    }

    protected function exportFileName(): string
    {
        return 'instrumentos-fuera-de-servicio';
    }

    protected function exportHeadings(): array
    {
        return ['Departamento', 'Ubicación', 'Equipo', 'Marca', 'Modelo', 'Código', 'Estado', 'Actualizado'];
    }

    protected function exportRows(): array
    {
        return Instrument::status(Instrument::ESTADO_FUERA_SERVICIO)->get()->map(fn (Instrument $i) => [
            $i->department,
            $i->location,
            $i->name,
            $i->brand,
            $i->model,
            $i->code,
            $i->status,
            $i->updated_at?->format('Y-m-d H:i'),
        ])->all();
    }

    public function layout(): array
    {
        return [
            Layout::table('instruments', [
                TD::make('department', 'Departamento'),
                TD::make('location', 'Ubicación'),
                TD::make('name', 'Equipo'),
                TD::make('brand', 'Marca'),
                TD::make('model', 'Modelo'),
                TD::make('code', 'Código')->sort()->filter()
                    ->render(fn ($i) => Link::make($i->code)->route('platform.instruments.view', $i->id)),
                TD::make('status', 'Estado'),
                TD::make('updated_at', 'Actualizado')->render(fn ($i) => $i->updated_at->diffForHumans()),
                TD::make('acciones', 'Acciones')
                    ->alignRight()
                    ->render(fn ($i) => Link::make('Histórico')
                        ->icon('bs.clock-history')
                        ->route('platform.instruments.events', $i->id)),
            ]),
        ];
    }
}
