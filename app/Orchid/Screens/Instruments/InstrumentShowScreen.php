<?php

namespace App\Orchid\Screens\Instruments;

use App\Models\Instrument;
use App\Models\InstrumentEvent;
use App\Orchid\Concerns\ExportsTable;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstrumentShowScreen extends Screen
{
    use ExportsTable;

    public $name = 'Detalle del Instrumento';

    public $description = 'Información general del instrumento y su historial de eventos.';

    public $instrument;

    public function query(Instrument $instrument): array
    {
        return [
            'instrument' => $instrument->load('events'),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('✏️ Editar')
                // ->icon('pencil')
                ->route('platform.instruments.edit', $this->instrument->id),

            ...$this->exportButtons(),
        ];
    }

    protected function exportFileName(): string
    {
        $instrument = request()->route('instrument');

        return 'instrumento-'.($instrument->code ?? $instrument->id).'-historial';
    }

    protected function exportHeadings(): array
    {
        return ['Tipo', 'Fecha', 'Responsable', 'Reporte', 'Adecuado', 'Próxima'];
    }

    protected function exportRows(): array
    {
        $instrument = request()->route('instrument');

        return $instrument->events()
            ->orderByDesc('fecha_evento')
            ->get()
            ->map(fn (InstrumentEvent $e) => [
                $e->event_type,
                $e->fecha_evento?->format('Y-m-d'),
                $e->responsable,
                $e->reporte,
                $e->adecuado ? 'Sí' : 'No',
                $e->fecha_proxima?->format('Y-m-d'),
            ])->all();
    }

    public function layout(): array
    {
        return [
            // 🧾 Información general del instrumento
            Layout::legend('instrument', [
                Sight::make('code', 'Código'),
                Sight::make('name', 'Nombre'),
                Sight::make('type', 'Tipo'),
                Sight::make('department', 'Departamento'),
                Sight::make('location', 'Ubicación'),
                Sight::make('form', 'Forma'),
                Sight::make('Variable Unidad De Medida', 'Variable / Unidad de Medida'),
                Sight::make('equipo', 'Equipo'),
                Sight::make('brand', 'Marca'),
                Sight::make('model', 'Modelo'),
                Sight::make('types_of_criticality', 'Tipo de criticidad')->render(fn ($i) => $i->types_of_criticality === 'CRITICO' ? '⚠️ Crítico' : 'No crítico'),
                Sight::make('level_of_criticality', 'Nivel de criticidad')->render(fn ($i) => ucfirst(strtolower($i->level_of_criticality))),
                Sight::make('is_operational', 'Operativo')->render(fn ($i) => $i->is_operational ? '✅ Sí' : '❌ No'),
                Sight::make('observations', 'Observaciones'),
            ])->title('Información del Instrumento'),

            // 📅 Fechas de calibración, validación y mantenimiento
            Layout::legend('instrument', [
                // 📅 Fechas de calibración
                Sight::make('last_calibration_date', 'Última Calibración')
                    ->render(fn ($i) => $i->last_calibration_date
                        ? $i->last_calibration_date->format('Y-m-d').
                        ($i->last_calibration_user ? ' — '.e($i->last_calibration_user) : '')
                        : 'Sin registro'),
                Sight::make('next_calibration_date', 'Próxima Calibración')
                    ->render(fn ($i) => $i->next_calibration_date
                        ? $i->next_calibration_date->format('Y-m-d')
                        : '—'),
                // 📅 Fechas de validación
                Sight::make('last_validation_date', 'Última Verificación')
                    ->render(fn ($i) => $i->last_validation_date
                        ? $i->last_validation_date->format('Y-m-d').
                        ($i->last_validation_user ? ' — '.e($i->last_validation_user) : '')
                        : 'Sin registro'),

                Sight::make('next_validation_date', 'Próxima Verificación')
                    ->render(fn ($i) => $i->next_validation_date
                        ? $i->next_validation_date->format('Y-m-d')
                        : '—'),
                // 📅 Fechas de mantenimiento
                Sight::make('last_maintenance_date', 'Último Mantenimiento')
                    ->render(fn ($i) => $i->last_maintenance_date
                        ? $i->last_maintenance_date->format('Y-m-d').
                        ($i->last_maintenance_user ? ' — '.e($i->last_maintenance_user) : '')
                        : 'Sin registro'),

                Sight::make('next_maintenance_date', 'Próximo Mantenimiento')
                    ->render(fn ($i) => $i->next_maintenance_date
                        ? $i->next_maintenance_date->format('Y-m-d')
                        : '—'),
            ])->title('Resumen de Fechas'),

            // 📋 Tabla con historial de eventos
            Layout::table('instrument.events', [
                TD::make('event_type', 'Tipo')->render(fn ($e) => match ($e->event_type) {
                    'CALIBRACION' => '📏 Calibración',
                    'VALIDACION' => '✅ Verificación',
                    'MANTENIMIENTO' => '🛠️ Mantenimiento',
                    default => $e->event_type,
                }),
                TD::make('fecha_evento', 'Fecha')->render(fn ($e) => $e->fecha_evento?->format('Y-m-d')),
                TD::make('responsable', 'Responsable'),
                TD::make('reporte', 'Reporte'),
                TD::make('adecuado', 'Adecuado')->render(fn ($e) => $e->adecuado ? '✅' : '❌'),
                TD::make('fecha_proxima', 'Próxima')->render(fn ($e) => $e->fecha_proxima?->format('Y-m-d')),
            ])->title('Historial de Eventos (Calibraciones / Validaciones / Mantenimientos)'),
        ];
    }
}
