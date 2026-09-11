<?php

namespace App\Orchid\Screens\Instruments;

use App\Models\Instrument;
use App\Orchid\Concerns\ExportsTable;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstrumentListScreen extends Screen
{
    use ExportsTable;

    public $name = 'Catálogo de Instrumentos';

    public $description = 'Listado general de instrumentos con detalles y estado operativo.';

    /**
     * Query del catálogo con los filtros activos (criticidad, vencimientos).
     * Reutilizada por la vista (paginada) y por la exportación (completa).
     */
    private function filteredQuery()
    {
        $query = Instrument::visibles()->criticality(request('types_of_criticality'));

        $due = request('due');
        $band = request('band');

        if ($due === 'any') {
            $query->overdueAny();
        } elseif (in_array($due, ['calibracion', 'verificacion', 'mantenimiento'], true) && $band) {
            $query->due($due, $band);
        }

        return $query;
    }

    public function query(): iterable
    {
        return [
            'instruments' => $this->filteredQuery()->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Nuevo Instrumento')
                ->icon('plus')
                ->route('platform.instruments.create'),

            ...$this->exportButtons(),
        ];
    }

    protected function exportFileName(): string
    {
        return 'instrumentos';
    }

    protected function exportHeadings(): array
    {
        return ['Departamento', 'Ubicación', 'Forma', 'Variable', 'Equipo', 'Marca', 'Modelo', 'Código', 'E.M.T.', 'Periodo calibración (días)', 'Instructivo', 'Estado'];
    }

    protected function exportRows(): array
    {
        return $this->filteredQuery()->get()->map(fn (Instrument $i) => [
            $i->department,
            $i->location,
            $i->form,
            $i->variable_unit_of_measure,
            $i->name,
            $i->brand,
            $i->model,
            $i->code,
            $i->emt_value,
            $i->calibration_periodicity_days,
            $i->file_manual,
            $i->status,
        ])->all();
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
