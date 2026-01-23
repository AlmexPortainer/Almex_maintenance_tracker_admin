<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class InstrumentEvent extends Model
{
    use AsSource, HasFactory;

    protected $fillable = [
        'instrument_id',
        'event_type',
        'fecha_evento',
        'responsable',
        'reporte',
        'resultados',
        'adecuado',
        'fecha_proxima',
        'fecha_maxima',
    ];

    protected $casts = [
        'fecha_evento' => 'date',
        'fecha_proxima' => 'date',
        'fecha_maxima' => 'date',
        'adecuado' => 'bool',
    ];

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }

    protected static function booted()
    {
        static::saved(function (InstrumentEvent $event) {
            $instrument = $event->instrument;
            if (! $instrument) {
                return;
            }

            // 🔄 Actualiza snapshot dependiendo del tipo de evento
            switch ($event->event_type) {
                case 'CALIBRACION':
                    $instrument->update([
                        'last_calibration_date' => $event->fecha_evento,
                        'last_calibration_user' => $event->responsable,
                        'next_calibration_date' => $event->fecha_proxima,
                    ]);
                    break;

                case 'VALIDACION':
                    $instrument->update([
                        'last_validation_date' => $event->fecha_evento,
                        'last_validation_user' => $event->responsable,
                        'next_validation_date' => $event->fecha_proxima,
                    ]);
                    break;

                case 'MANTENIMIENTO':
                    $instrument->update([
                        'last_maintenance_date' => $event->fecha_evento,
                        'last_maintenance_user' => $event->responsable,
                        'next_maintenance_date' => $event->fecha_proxima,
                    ]);
                    break;
            }
        });

        // 🗑️ Si se elimina un evento, recalcula los snapshots
        static::deleted(function (InstrumentEvent $event) {
            $instrument = $event->instrument;
            if (! $instrument) {
                return;
            }

            // Busca el último evento restante del mismo tipo
            $latest = $instrument->events()
                ->where('event_type', $event->event_type)
                ->orderByDesc('fecha_evento')
                ->first();

            switch ($event->event_type) {
                case 'CALIBRACION':
                    $instrument->update([
                        'last_calibration_date' => $latest?->fecha_evento,
                        'last_calibration_user' => $latest?->responsable,
                        'next_calibration_date' => $latest?->fecha_proxima,
                    ]);
                    break;

                case 'VALIDACION':
                    $instrument->update([
                        'last_validation_date' => $latest?->fecha_evento,
                        'last_validation_user' => $latest?->responsable,
                        'next_validation_date' => $latest?->fecha_proxima,
                    ]);
                    break;

                case 'MANTENIMIENTO':
                    $instrument->update([
                        'last_maintenance_date' => $latest?->fecha_evento,
                        'last_maintenance_user' => $latest?->responsable,
                        'next_maintenance_date' => $latest?->fecha_proxima,
                    ]);
                    break;
            }
        });
    }
}
