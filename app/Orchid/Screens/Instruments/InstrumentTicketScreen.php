<?php

namespace App\Orchid\Screens\Instruments;

use App\Models\Instrument;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class InstrumentTicketScreen extends Screen
{
    public function name(): string
    {
        return 'Impresión de Ticket';
    }

    public function query(Request $request): iterable
    {
        $instrument = $request->filled('instrument')
            ? Instrument::find($request->input('instrument'))
            : null;

        $data = [
            'selected' => $instrument?->id,
        ];

        if (! $instrument) {
            return $data;
        }

        return array_merge($data, [
            // ===== Datos base =====
            'equipo' => $instrument->equipo,
            'marca' => $instrument->brand,
            'modelo' => $instrument->model,
            'codigo' => $instrument->code,

            // ===== Estado =====
            'apto' => (bool) $instrument->is_operational,

            // ===== CALIBRACIÓN =====
            'cal_ultima' => optional($instrument->last_calibration_date)?->format('d/m/Y'),
            'cal_proxima' => optional($instrument->next_calibration_date)?->format('d/m/Y'),
            'cal_usuario' => $instrument->last_calibration_user,
            'cal_requiere' => $instrument->calibrationRequired(),

            // ===== VERIFICACIÓN =====
            'val_ultima' => optional($instrument->last_validation_date)?->format('d/m/Y'),
            'val_proxima' => optional($instrument->next_validation_date)?->format('d/m/Y'),
            'val_usuario' => $instrument->last_validation_user,
            'val_requiere' => $instrument->validationRequired(),

            // ===== MANTENIMIENTO =====
            'mnt_ultima' => optional($instrument->last_maintenance_date)?->format('d/m/Y'),
            'mnt_proxima' => optional($instrument->next_maintenance_date)?->format('d/m/Y'),
            'mnt_usuario' => $instrument->last_maintenance_user,
            'mnt_requiere' => $instrument->maintenanceRequired(),
        ]);
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        $layouts = [
            Layout::rows([
                Select::make('instrument')
                    ->options(Instrument::orderBy('code')->get()
                        ->mapWithKeys(fn (Instrument $i) => [$i->id => "{$i->code} - {$i->name}"])
                        ->toArray())
                    ->empty('— Selecciona un instrumento —')
                    ->searchable()
                    ->title('Buscar instrumento (código - nombre)')
                    ->value(request('instrument')),

                Button::make('Ver ticket')
                    ->method('show')
                    ->icon('printer'),
            ]),
        ];

        if (request()->filled('instrument')) {
            $layouts[] = Layout::view('prints.ticket-medicion');
        }

        return $layouts;
    }

    public function show(Request $request)
    {
        return redirect()->route('platform.instruments.tickets', [
            'instrument' => $request->input('instrument'),
        ]);
    }
}