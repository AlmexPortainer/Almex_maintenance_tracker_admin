<?php

namespace App\Orchid\Screens\InstrumentEvents;

use App\Models\Instrument;
use App\Models\InstrumentEvent;
use App\Orchid\Layouts\InstrumentEvents\InstrumentEventNextDateListener;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class InstrumentEventCreateScreen extends screen
{
    public $name = 'Evento de Instrumento';

    public $description = 'Registrar o editar calibraciones, validaciones o mantenimientos.';

    public $instrumentEvent;

    public function query(string $event_type): iterable
    {
        return [
            'eventType' => $event_type,
            // Preselecciona el tipo según el menú de origen (CALIBRACION/VALIDACION/MANTENIMIENTO).
            'instrumentEvent' => [
                'event_type' => $event_type,
            ],
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('💾 Guardar')
                ->method('save'),
        ];
    }

    public function layout(): array
    {
        return [
            InstrumentEventNextDateListener::class,

            Layout::rows([
                TextArea::make('instrumentEvent.resultados')
                    ->title('Resultados')
                    ->rows(3),
            ]),
        ];
    }

    private function getFrequencyDaysForEventType(Instrument $instrument, string $eventType): ?int
    {
        return match ($eventType) {
            'CALIBRACION' => $instrument->calibration_periodicity_days,
            'VALIDACION' => $instrument->validation_periodicity_days,
            'MANTENIMIENTO' => $instrument->maintenance_periodicity_days,
            default => null,
        };
    }

    private function calculateFechaProxima(?string $fechaEvento, ?int $freqDays): ?Carbon
    {
        if (empty($fechaEvento) || $freqDays === null) {
            return null;
        }

        return Carbon::parse($fechaEvento)->addDays((int) $freqDays);
    }

    public function save(Request $request, InstrumentEvent $instrumentEvent)
    {
        $validated = $request->validate([
            'instrumentEvent.instrument_id' => 'required|exists:instruments,id',
            'instrumentEvent.event_type' => 'required|in:CALIBRACION,VALIDACION,MANTENIMIENTO',
            'instrumentEvent.fecha_evento' => 'required|date',
            'instrumentEvent.responsable' => 'nullable|string|max:255',
            'instrumentEvent.reporte' => 'nullable|string|max:255',
            'instrumentEvent.resultados' => 'nullable|string',
            'instrumentEvent.adecuado' => 'boolean',
            'instrumentEvent.fecha_proxima' => 'nullable|date',
            'instrumentEvent.fecha_maxima' => 'nullable|date',
        ]);

        $data = $validated['instrumentEvent'];

        $instrument = Instrument::find($data['instrument_id']);
        if ($instrument) {
            $freqDays = $this->getFrequencyDaysForEventType($instrument, (string) $data['event_type']);
            $fechaProxima = $this->calculateFechaProxima((string) $data['fecha_evento'], $freqDays);

            if ($fechaProxima) {
                $data['fecha_proxima'] = $fechaProxima;
            }
        }

        $instrumentEvent->fill($data);
        $instrumentEvent->save();

        Alert::success('Evento guardado correctamente.');

        if ($instrumentEvent->instrument_id) {
            return redirect()->route('platform.instruments.view', $instrumentEvent->instrument_id);
        }

        return redirect()->route('platform.instrument_events.global');
    }
}
