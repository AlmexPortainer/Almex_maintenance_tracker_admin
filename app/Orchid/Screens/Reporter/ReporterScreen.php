<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Reporter;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ReporterScreen extends Screen
{
    public string $tipo = '';

    public string $area = '';

    public string $name = 'Reportería';

    public function query(string $tipo, string $area): array
    {
        $this->tipo = $tipo;
        $this->area = $area;

        $this->name = ucfirst($tipo).' - '.ucfirst($area);

        return [
            'tipo' => $tipo,
            'area' => $area,
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::view('reporter', [
                'tipo' => $this->tipo,
                'area' => $this->area,
            ]),
        ];
    }
}
