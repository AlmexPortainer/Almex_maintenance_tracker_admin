<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\InstrumentEvents;

use App\Models\Instrument;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class InstrumentEventNextDateListener extends Listener
{
    protected $targets = [
        'instrumentEvent.instrument_id',
        'instrumentEvent.event_type',
        'instrumentEvent.fecha_evento',
    ];

    protected function layouts(): array
    {
        return [
            Layout::rows([
                Select::make('instrumentEvent.instrument_id')
                    ->fromQuery(Instrument::query(), 'name', 'id')
                    ->title('Instrumento')
                    ->required(),

                Select::make('instrumentEvent.event_type')
                    ->options([
                        'CALIBRACION' => '📏 Calibración',
                        'VALIDACION' => '✅ Verificación',
                        'MANTENIMIENTO' => '🛠️ Mantenimiento',
                    ])
                    ->title('Tipo de Evento')
                    ->required(),

                DateTimer::make('instrumentEvent.fecha_evento')
                    ->title('Fecha del Evento')
                    ->allowInput()
                    ->format('Y-m-d')
                    ->required(),

                // ✅ Solo muestra lo que venga en "freq_days"
                Label::make('freq_days')
                    ->title('Frecuencia aplicada (días)'),

                DateTimer::make('instrumentEvent.fecha_proxima')
                    ->title('Fecha Próxima')
                    ->format('Y-m-d')
                    ->readonly(),

                Input::make('instrumentEvent.responsable')
                    ->title('Responsable'),

                Input::make('instrumentEvent.reporte')
                    ->title('Reporte'),
            ]),
        ];
    }

    public function handle(Repository $repository, Request $request): Repository
    {
        $current = (array) $repository->get('instrumentEvent', []);
        $incoming = (array) $request->input('instrumentEvent', []);
        $repository = $repository->set('instrumentEvent', array_replace($current, $incoming));

        $instrumentId = $repository->get('instrumentEvent.instrument_id');
        $eventType = (string) $repository->get('instrumentEvent.event_type');
        $fechaEvento = $repository->get('instrumentEvent.fecha_evento');

        // ✅ placeholder por defecto (evita null/array)
        $repository = $repository->set('freq_days', '—');

        if (! $instrumentId || $eventType === '' || empty($fechaEvento)) {
            return $repository;
        }

        $instrument = Instrument::find($instrumentId);
        if (! $instrument) {
            return $repository;
        }

        $freqDays = match ($eventType) {
            'CALIBRACION' => $instrument->calibration_periodicity_days,
            'VALIDACION' => $instrument->validation_periodicity_days,
            'MANTENIMIENTO' => $instrument->maintenance_periodicity_days,
            default => null,
        };

        if ($freqDays === null) {
            return $repository;
        }

        $fechaProxima = Carbon::parse((string) $fechaEvento)
            ->addDays((int) $freqDays)
            ->format('Y-m-d');

        return $repository
            ->set('freq_days', (string) ((int) $freqDays))
            ->set('instrumentEvent.fecha_proxima', $fechaProxima);
    }
}
