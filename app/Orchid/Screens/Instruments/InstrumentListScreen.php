<?php

namespace App\Orchid\Screens\Instruments;

use App\Models\Instrument;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstrumentListScreen extends Screen
{
    public $name = 'Catálogo de Instrumentos';

    public $description = 'Listado general de instrumentos con detalles y estado operativo.';

    public function query(): array
    {
        return [
            'instruments' => Instrument::paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Nuevo Instrumento')
                ->icon('plus')
                ->route('platform.instruments.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('instruments', [
                TD::make('department', 'Departamento'),
                TD::make('location', 'Ubicación'),
                TD::make('type', 'Forma'),
                TD::make('variable_unit_of_measure', 'Variable'),
                TD::make('name', 'Equipo'),
                TD::make('brand', 'Marca'),
                TD::make('model', 'Modelo'),
                TD::make('code', 'Código')->sort()->filter()
                    ->render(fn ($i) => Link::make($i->code)->route('platform.instruments.view', $i->id)),
                TD::make('emt_value', 'E.M.T.'),

                TD::make('calibration_periodicity_days', 'Periodo de calibracion'),

                TD::make('file_manual', 'Instructivo'),
                TD::make('updated_at', 'Actualizado')->render(fn ($i) => $i->updated_at->diffForHumans()),
            ]),
        ];
    }
}
