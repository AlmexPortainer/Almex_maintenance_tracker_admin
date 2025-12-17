<?php

namespace App\Orchid\Screens\InstrumentEvents;

use App\Models\InstrumentEvent;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstrumentEventListScreen extends Screen
{
    public $name = 'Historial de Eventos de Instrumentos';

    public $description = 'Calibraciones, validaciones y mantenimientos registrados.';

    public function query(): array
    {

        return [
            'events' => InstrumentEvent::paginate(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('➕ Registrar Evento')
                ->icon('plus')
                ->route('platform.instrument_events.create'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('events', [
                TD::make('event_type', 'Tipo')
                    ->render(fn ($e) => Link::make(match ($e->event_type) {
                        'CALIBRACION' => '📏 Calibración',
                        'VALIDACION' => '✅ Verificación',
                        'MANTENIMIENTO' => '🛠️ Mantenimiento',
                        default => $e->event_type,
                    })->route('platform.instrument_events.view', $e->id)),

                TD::make('instrument.code', 'Instrumento')->render(fn ($e) => Link::make($e->instrument->code)->route('platform.instruments.view', $e->instrument->id)),
                TD::make('fecha_evento', 'Fecha')->render(fn ($e) => $e->fecha_evento->format('Y-m-d')),
                TD::make('responsable', 'Responsable'),
                TD::make('reporte', 'Reporte'),
                TD::make('adecuado', 'Adecuado')->render(fn ($e) => $e->adecuado ? '✅' : '❌'),
                TD::make('fecha_proxima', 'Próxima')->render(fn ($e) => $e->fecha_proxima?->format('Y-m-d')),
            ]),
        ];
    }
}
