<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Instrument;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /** Tipos de evento => etiqueta. */
    private const TYPES = [
        'calibracion' => 'Calibración',
        'verificacion' => 'Verificación',
        'mantenimiento' => 'Mantenimiento',
    ];

    /** Bandas de horizonte (exclusivas). */
    private const BANDS = ['vencido', 'b0_15', 'b16_30', 'b31_90'];

    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        $total = Instrument::count();
        $operativos = Instrument::where('is_operational', true)->count();

        $matrix = [];
        $requieren = [];

        foreach (array_keys(self::TYPES) as $tipo) {
            foreach (self::BANDS as $band) {
                $matrix[$tipo][$band] = Instrument::due($tipo, $band)->count();
            }
            $requieren[$tipo] = Instrument::where(
                Instrument::typeFields($tipo)['period'], '>', 0
            )->count();
        }

        return [
            // ===== Sección 1: Todos los instrumentos =====
            'total' => $total,
            'operativos' => $operativos,
            'no_operativos' => $total - $operativos,
            'criticos' => Instrument::where('types_of_criticality', 'CRITICO')->count(),
            'vencidos_total' => Instrument::overdueAny()->count(),

            // ===== Sección 2: Matriz por tipo × horizonte =====
            'types' => self::TYPES,
            'bands' => self::BANDS,
            'matrix' => $matrix,
            'requieren' => $requieren,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Tablero de Instrumentos';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Resumen de estado y esfuerzo de calibración, verificación y mantenimiento.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('dashboard.instrument-cards'),
        ];
    }
}