<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Instrument extends Model
{
    use AsSource, Filterable, HasFactory;

    /* =====================================================
     | CONFIGURACIÓN BASE
     ===================================================== */
    protected $table = 'instruments';

    /* =====================================================
     | CONSTANTES DE NEGOCIO
     ===================================================== */
    // Días de advertencia antes del vencimiento
    public const WARNING_DAYS_DEFAULT = 15;

    // Preparadas para futura granularidad
    public const WARNING_DAYS_CALIBRATION = 15;

    public const WARNING_DAYS_VALIDATION = 15;

    public const WARNING_DAYS_MAINTENANCE = 15;

    // Estados operativos
    public const STATUS_OK = 'OK';

    public const STATUS_PROXIMO = 'PROXIMO';

    public const STATUS_VENCIDO = 'VENCIDO';

    public const STATUS_NO_REQUIERE = 'NO_REQUIERE';

    /* =====================================================
     | ATRIBUTOS MASS ASSIGNMENT
     ===================================================== */

    protected $fillable = [
        // Identidad
        'name',
        'type',
        'equipo',
        'brand',
        'model',
        'code',

        // Clasificación
        'department',
        'location',
        'form',
        'types_of_criticality',
        'level_of_criticality',

        // Medición
        'emt_value',
        'emt_value_decimal',
        'emt_unit',
        'emt_symmetry',

        // Documentación
        'file_manual',

        // Snapshots de calibración
        'last_calibration_date',
        'last_calibration_user',
        'next_calibration_date',

        // Snapshots de verificación
        'last_validation_date',
        'last_validation_user',
        'next_validation_date',

        // Snapshots de mantenimiento
        'last_maintenance_date',
        'last_maintenance_user',
        'next_maintenance_date',

        // Operación
        'calibration_periodicity_days',
        'validation_periodicity_days',
        'maintenance_periodicity_days',
        'is_operational',
        'observations',
    ];

    /* =====================================================
     | CASTS
     ===================================================== */

    protected $casts = [
        'emt_value_decimal' => 'decimal:4',
        'emt_symmetry' => 'bool',
        'is_operational' => 'bool',

        'last_calibration_date' => 'date',
        'next_calibration_date' => 'date',
        'last_validation_date' => 'date',
        'next_validation_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',

        'calibration_periodicity_days' => 'integer',
        'validation_periodicity_days' => 'integer',
        'maintenance_periodicity_days' => 'integer',
    ];

    /* =====================================================
     | ORCHID: FILTROS Y ORDENAMIENTO
     ===================================================== */

    protected $allowedSorts = [
        'name',
        'type',
        'department',
        'location',
        'brand',
        'model',
        'types_of_criticality',
        'level_of_criticality',
        'next_calibration_date',
    ];

    protected $allowedFilters = [
        'name',
        'type',
        'department',
        'location',
        'brand',
        'model',
        'types_of_criticality',
        'level_of_criticality',
        'is_operational',
    ];

    /* =====================================================
     | RELACIONES
     ===================================================== */

    public function events()
    {
        return $this->hasMany(InstrumentEvent::class);
    }

    public function calibrations()
    {
        return $this->events()->where('event_type', 'CALIBRACION');
    }

    public function validations()
    {
        return $this->events()->where('event_type', 'VALIDACION');
    }

    public function maintenances()
    {
        return $this->events()->where('event_type', 'MANTENIMIENTO');
    }

    /* =====================================================
     | SCOPES
     ===================================================== */

    public function scopeCriticality($query, ?string $criticality): void
    {
        if ($criticality) {
            $query->where('types_of_criticality', $criticality);
        }
    }

    /* =====================================================
     | LÓGICA DE NEGOCIO: ESTADOS
     ===================================================== */

    /**
     * Estado genérico de un evento basado en la fecha NEXT
     */
    protected function eventStatus(
        ?Carbon $nextDate,
        int $periodicityDays
    ): string {
        if ($periodicityDays <= 0 || ! $nextDate) {
            return self::STATUS_NO_REQUIERE;
        }

        $today = Carbon::today();

        if ($nextDate->lt($today)) {
            return self::STATUS_VENCIDO;
        }

        if ($nextDate->lte(
            $today->copy()->addDays(self::WARNING_DAYS_DEFAULT)
        )) {
            return self::STATUS_PROXIMO;
        }

        return self::STATUS_OK;
    }

    /* =====================================================
     | REQUIERE EVENTO (SI / NO)
     ===================================================== */

    public function calibrationRequired(): bool
    {
        return $this->calibration_periodicity_days > 0;
    }

    public function validationRequired(): bool
    {
        return $this->validation_periodicity_days > 0;
    }

    public function maintenanceRequired(): bool
    {
        return $this->maintenance_periodicity_days > 0;
    }

    /* =====================================================
     | ESTADO POR TIPO
     ===================================================== */

    public function calibrationStatus(): string
    {
        return $this->eventStatus(
            $this->next_calibration_date,
            $this->calibration_periodicity_days
        );
    }

    public function validationStatus(): string
    {
        return $this->eventStatus(
            $this->next_validation_date,
            $this->validation_periodicity_days
        );
    }

    public function maintenanceStatus(): string
    {
        return $this->eventStatus(
            $this->next_maintenance_date,
            $this->maintenance_periodicity_days
        );
    }

    /* =====================================================
     | HORIZONTES DE VENCIMIENTO (DASHBOARD / REPORTERÍA)
     ===================================================== */

    /**
     * Campos next_/period_ según el tipo de evento.
     */
    public static function typeFields(string $tipo): array
    {
        return match ($tipo) {
            'verificacion' => ['next' => 'next_validation_date', 'period' => 'validation_periodicity_days'],
            'mantenimiento' => ['next' => 'next_maintenance_date', 'period' => 'maintenance_periodicity_days'],
            default => ['next' => 'next_calibration_date', 'period' => 'calibration_periodicity_days'],
        };
    }

    /**
     * Instrumentos que requieren un evento y caen en una banda de horizonte.
     * Bandas exclusivas: vencido | b0_15 | b16_30 | b31_90.
     */
    public function scopeDue($query, string $tipo, string $band): void
    {
        $f = self::typeFields($tipo);
        $today = Carbon::today();
        $d = fn (int $n): string => $today->copy()->addDays($n)->toDateString();

        $query->where($f['period'], '>', 0)->whereNotNull($f['next']);

        match ($band) {
            'vencido' => $query->whereDate($f['next'], '<', $d(0)),
            'b0_15' => $query->whereBetween($f['next'], [$d(0), $d(15)]),
            'b16_30' => $query->whereBetween($f['next'], [$d(16), $d(30)]),
            'b31_90' => $query->whereBetween($f['next'], [$d(31), $d(90)]),
            default => null,
        };
    }

    /**
     * Instrumentos con al menos un evento vencido (cualquier tipo).
     */
    public function scopeOverdueAny($query): void
    {
        $today = Carbon::today()->toDateString();

        $sets = [
            ['calibration_periodicity_days', 'next_calibration_date'],
            ['validation_periodicity_days', 'next_validation_date'],
            ['maintenance_periodicity_days', 'next_maintenance_date'],
        ];

        $query->where(function ($q) use ($sets, $today) {
            foreach ($sets as [$period, $next]) {
                $q->orWhere(function ($qq) use ($period, $next, $today) {
                    $qq->where($period, '>', 0)->whereDate($next, '<', $today);
                });
            }
        });
    }
}
