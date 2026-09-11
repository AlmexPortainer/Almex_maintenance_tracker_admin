<?php

use Database\Seeders\InstrumentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! file_exists(base_path('database/mdb_export/Instrumentos.json'))) {
        $this->markTestSkipped('Faltan los JSON de database/mdb_export (extracción Access).');
    }
});

it('imports all instruments with mapped fields', function () {
    (new InstrumentImportSeeder)->run();

    expect(DB::table('instruments')->count())->toBe(777);

    $i = DB::table('instruments')->where('code', '048012')->first();
    expect($i->name)->toBe('Balanza andén PT4')
        ->and($i->brand)->toBe('WEIGH-TRONIX')
        ->and($i->model)->toBe('W1-125')
        ->and($i->department)->toBe('Mantenimiento')
        ->and($i->location)->toBe('Producto Terminado')
        ->and($i->form)->toBe('Instrumento Electrónico')
        ->and($i->variable_unit_of_measure)->toBe('Peso')
        ->and($i->types_of_criticality)->toBe('NO_CRITICO')
        ->and((bool) $i->is_operational)->toBeTrue()
        ->and($i->emt_value)->toBe('+/- 0.150 Kg')
        ->and((int) $i->calibration_periodicity_days)->toBe(30)
        ->and((int) $i->validation_periodicity_days)->toBe(0)
        ->and((int) $i->maintenance_periodicity_days)->toBe(30)
        ->and($i->last_calibration_date)->toBe('2026-08-17')
        ->and($i->next_calibration_date)->toBe('2026-09-16')
        ->and($i->file_manual)->toBe('N/A')
        ->and($i->status)->toBe('Activo')
        ->and($i->observations)->toBe('Incertidumbre de el  peso de 100 Kg.');
});

it('stores status and excludes Baja/Fuera de Servicio from the visible catalog', function () {
    (new InstrumentImportSeeder)->run();

    expect(\App\Models\Instrument::where('status', 'Activo')->count())->toBe(382)
        ->and(\App\Models\Instrument::where('status', 'Baja')->count())->toBe(292)
        ->and(\App\Models\Instrument::where('status', 'Fuera de Servicio')->count())->toBe(90)
        ->and(\App\Models\Instrument::where('status', 'Stock')->count())->toBe(13);

    // El catálogo principal (scope visibles) = Activo + Stock = 395.
    expect(\App\Models\Instrument::visibles()->count())->toBe(395);
});

it('imports historical events, skipping orphans and null dates', function () {
    (new InstrumentImportSeeder)->run();

    expect(DB::table('instrument_events')->count())->toBe(25403)
        ->and(DB::table('instrument_events')->where('event_type', 'CALIBRACION')->count())->toBe(9519)
        ->and(DB::table('instrument_events')->where('event_type', 'VALIDACION')->count())->toBe(11752)
        ->and(DB::table('instrument_events')->where('event_type', 'MANTENIMIENTO')->count())->toBe(4132);

    $id = DB::table('instruments')->where('code', '048012')->value('id');
    $events = DB::table('instrument_events')->where('instrument_id', $id);
    expect((clone $events)->where('event_type', 'CALIBRACION')->count())->toBe(366)
        ->and((clone $events)->where('event_type', 'VALIDACION')->count())->toBe(0)
        ->and((clone $events)->where('event_type', 'MANTENIMIENTO')->count())->toBe(365);

    // El hook saved NO corre: snapshot queda como Access, no recalculado.
    expect(DB::table('instruments')->where('code', '048012')->value('last_calibration_date'))
        ->toBe('2026-08-17');
});
