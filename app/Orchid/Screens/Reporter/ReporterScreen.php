<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Reporter;

use App\Models\Instrument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ReporterScreen extends Screen
{
    /** Ventanas de horizonte disponibles (días). */
    private const WINDOWS = [30, 60, 90];

    public string $tipo = '';

    public string $area = '';

    public int $days = 30;

    public string $name = 'Reportería';

    /**
     * Campos y método de estado según el tipo de evento.
     */
    private function fieldsFor(string $tipo): array
    {
        return match ($tipo) {
            'verificacion' => [
                'last' => 'last_validation_date',
                'next' => 'next_validation_date',
                'period' => 'validation_periodicity_days',
                'status' => 'validationStatus',
            ],
            'mantenimiento' => [
                'last' => 'last_maintenance_date',
                'next' => 'next_maintenance_date',
                'period' => 'maintenance_periodicity_days',
                'status' => 'maintenanceStatus',
            ],
            default => [ // calibracion
                'last' => 'last_calibration_date',
                'next' => 'next_calibration_date',
                'period' => 'calibration_periodicity_days',
                'status' => 'calibrationStatus',
            ],
        };
    }

    /**
     * Etiqueta de departamento a partir del área de la ruta.
     */
    private function areaLabel(string $area): string
    {
        return match ($area) {
            'produccion' => 'Producción',
            'calidad' => 'Calidad',
            'servicios' => 'Servicios',
            default => ucfirst($area),
        };
    }

    public function query(string $tipo, string $area, Request $request): array
    {
        $this->tipo = $tipo;
        $this->area = $area;

        $days = (int) $request->input('days', 30);
        $this->days = in_array($days, self::WINDOWS, true) ? $days : 30;

        $this->name = ucfirst($tipo).' - '.$this->areaLabel($area);

        $fields = $this->fieldsFor($tipo);
        $limitDate = Carbon::today()->addDays($this->days);

        $instruments = Instrument::query()
            ->where('department', $this->areaLabel($area))
            ->where($fields['period'], '>', 0)
            ->whereNotNull($fields['next'])
            ->whereDate($fields['next'], '<=', $limitDate)
            ->orderBy($fields['next'])
            ->paginate(30);

        return [
            'instruments' => $instruments,
        ];
    }

    public function layout(): iterable
    {
        $fields = $this->fieldsFor($this->tipo);

        return [
            Layout::rows([
                Select::make('days')
                    ->options([30 => '30 días', 60 => '60 días', 90 => '90 días'])
                    ->value($this->days)
                    ->title('Horizonte (incluye vencidos)'),

                Button::make('Aplicar')
                    ->method('applyFilter')
                    ->icon('bs.funnel'),
            ]),

            Layout::table('instruments', [
                TD::make('code', 'Código')->sort(),
                TD::make('name', 'Nombre')->sort(),
                TD::make('equipo', 'Equipo'),

                TD::make($fields['last'], 'Última')
                    ->render(fn (Instrument $i) => optional($i->{$fields['last']})?->format('d/m/Y') ?? '—'),

                TD::make($fields['next'], 'Próxima')->sort()
                    ->render(fn (Instrument $i) => optional($i->{$fields['next']})?->format('d/m/Y') ?? '—'),

                TD::make('estado', 'Estado')
                    ->render(fn (Instrument $i) => $this->statusBadge($i, $fields)),

                TD::make('department', 'Departamento'),
                TD::make('location', 'Ubicación'),
            ]),
        ];
    }

    /**
     * Badge de estado + días restantes / vencido hace X.
     */
    private function statusBadge(Instrument $instrument, array $fields): string
    {
        $status = $instrument->{$fields['status']}();
        $next = $instrument->{$fields['next']};

        $map = [
            Instrument::STATUS_VENCIDO => 'bg-danger',
            Instrument::STATUS_PROXIMO => 'bg-warning text-dark',
            Instrument::STATUS_OK => 'bg-success',
            Instrument::STATUS_NO_REQUIERE => 'bg-secondary',
        ];
        $class = $map[$status] ?? 'bg-secondary';

        $label = $status;
        if ($next) {
            $diff = Carbon::today()->diffInDays($next, false);
            $label = $diff < 0
                ? $status.' · vencido hace '.abs($diff).' d'
                : $status.' · en '.$diff.' d';
        }

        return '<span class="badge '.$class.'">'.e($label).'</span>';
    }

    public function applyFilter(Request $request)
    {
        return redirect()->route('platform.reporter', [
            'tipo' => $this->tipo ?: $request->route('tipo'),
            'area' => $this->area ?: $request->route('area'),
            'days' => $request->input('days'),
        ]);
    }
}