<?php

namespace App\Orchid\Screens\Instruments;

use App\Models\Instrument;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class InstrumentTicketScreen extends Screen
{
    public function name(): string
    {
        return 'Impresión de Ticket';
    }

    public function query(): iterable
    {
        $instrument = Instrument::findOrFail(1);

        return [
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

        ];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('prints.ticket-medicion'),
        ];
    }
}
