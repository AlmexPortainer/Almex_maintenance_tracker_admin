<?php

namespace App\Orchid\Screens\Instruments;

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
        return [
            'equipo' => 'Calibrador Vernier',
            'marca' => 'MITUTOYO',
            'modelo' => '500-196-30',
            'codigo' => 'EQ-00123',
            'apto' => 'no-apto',
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
