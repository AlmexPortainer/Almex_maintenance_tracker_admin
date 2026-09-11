# Carga inicial desde Access Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Cargar los 777 instrumentos y ~25.4k eventos históricos del Access `BD InstrumentosCalidad2.mdb` a las tablas `instruments` e `instrument_events` vía un seeder reproducible.

**Architecture:** Los datos ya fueron extraídos a JSON UTF-8 en `database/mdb_export/` (herramienta Jackcess en `scripts/`, gitignored). Una migración expande los enums de `instruments`; un seeder lee los JSON y hace bulk `DB::table()->insert()` por chunks, evitando el hook `saved` de `InstrumentEvent`, tomando los snapshots directo de Access.

**Tech Stack:** Laravel 12, MySQL (prod) / SQLite in-memory (tests), Pest.

## Global Constraints

- Laravel 12 + MySQL en prod; tests corren en SQLite `:memory:` con Pest (`phpunit.xml`).
- `->change()` es nativo en Laravel 12 — NO agregar `doctrine/dbal`.
- El seeder consume `database/mdb_export/*.json` (UTF-8, gitignored). Si faltan, los tests se marcan skipped.
- Bulk insert vía `DB::table()` — nunca via modelo Eloquent (evitar hook `saved` de `InstrumentEvent`).
- `fecha_evento` es NOT NULL: eventos con fecha null se saltan.
- Eventos cuyo `Código` no existe en `instruments` (huérfanos) se saltan.
- Conteos esperados (archivo actual): **777** instrumentos; eventos válidos: **CALIBRACION 9519, VALIDACION 11752, MANTENIMIENTO 4132** (total **25403**).
- Fixture determinista — code `048012` ("Balanza andén PT4"): 366 CALIBRACION, 0 VALIDACION, 365 MANTENIMIENTO.

---

### Task 1: Migración — expandir enums de `instruments`

**Files:**
- Create: `database/migrations/2026_09_10_000000_expand_instrument_enums.php`
- Test: `tests/Feature/ExpandInstrumentEnumsTest.php`

**Interfaces:**
- Produces: columna `instruments.form` acepta `Instrumento Electrónico|Instrumento Mecánico|Instrumento Simple|Reactivo|Otro`; columna `instruments.variable_unit_of_measure` es `string` nullable (cualquier texto).

- [ ] **Step 1: Write the failing test**

`tests/Feature/ExpandInstrumentEnumsTest.php`:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('accepts expanded form values and free-text variable', function () {
    DB::table('instruments')->insert([
        'code' => 'TEST-ENUM-1',
        'form' => 'Reactivo',
        'variable_unit_of_measure' => 'Tamaño de particula',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $row = DB::table('instruments')->where('code', 'TEST-ENUM-1')->first();
    expect($row->form)->toBe('Reactivo')
        ->and($row->variable_unit_of_measure)->toBe('Tamaño de particula');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ExpandInstrumentEnumsTest`
Expected: FAIL — insert rechazado por el enum original (`form` no incluye `Reactivo`; `variable_unit_of_measure` enum no incluye ese texto).

- [ ] **Step 3: Write the migration**

`database/migrations/2026_09_10_000000_expand_instrument_enums.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->enum('form', [
                'Instrumento Electrónico',
                'Instrumento Mecánico',
                'Instrumento Simple',
                'Reactivo',
                'Otro',
            ])->default('Instrumento Simple')->change();

            $table->string('variable_unit_of_measure')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->enum('form', [
                'Instrumento Electronico',
                'Instrumento Mecánico',
                'Instrumento Simple',
            ])->default('Instrumento Simple')->change();

            $table->enum('variable_unit_of_measure', [
                'Conductividad', 'Concentración', 'Flujo', 'Humedad',
                'Indice de Refracción', 'KVA', 'MVP', 'N/A', 'Peso',
                'Presión', 'Temperatura', 'Transmitancia', 'pH',
            ])->default('N/A')->nullable()->change();
        });
    }
};
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ExpandInstrumentEnumsTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_10_000000_expand_instrument_enums.php tests/Feature/ExpandInstrumentEnumsTest.php
git commit -m "feat(instruments): expand form enum and make variable free-text"
```

---

### Task 2: `InstrumentImportSeeder` — importar instrumentos

**Files:**
- Create: `database/seeders/InstrumentImportSeeder.php`
- Test: `tests/Feature/InstrumentImportSeederTest.php`

**Interfaces:**
- Produces: clase `Database\Seeders\InstrumentImportSeeder` con `run(): void`. Tras `run()`, la tabla `instruments` contiene 777 filas mapeadas desde `database/mdb_export/Instrumentos.json`. Métodos privados `mapForm(?string): string`, `mapCriticality(?string): string`, `date(?string): ?string` reutilizados en Task 3.

- [ ] **Step 1: Write the failing test**

`tests/Feature/InstrumentImportSeederTest.php`:

```php
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
        ->and($i->observations)->toBe('Incertidumbre de el  peso de 100 Kg.');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=InstrumentImportSeederTest`
Expected: FAIL — `Class "Database\Seeders\InstrumentImportSeeder" not found`.

- [ ] **Step 3: Write the seeder (parte instrumentos)**

`database/seeders/InstrumentImportSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstrumentImportSeeder extends Seeder
{
    private string $dir;

    public function run(): void
    {
        $this->dir = base_path('database/mdb_export');

        // Idempotencia: limpiar destino respetando FK.
        Schema::disableForeignKeyConstraints();
        DB::table('instrument_events')->truncate();
        DB::table('instruments')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->importInstruments();
    }

    private function importInstruments(): void
    {
        $rows = $this->readJson('Instrumentos.json');
        $now = now();
        $out = [];

        foreach ($rows as $r) {
            $out[] = [
                'name' => $r['Equipo'] ?? null,
                'type' => 'INSTRUMENTO',
                'equipo' => $r['Equipo'] ?? null,
                'brand' => $r['Marca'] ?? null,
                'model' => $r['Modelo'] ?? null,
                'code' => $r['Código'],
                'department' => $r['Depto'] ?? null,
                'location' => $r['Area'] ?? null,
                'form' => $this->mapForm($r['FormaInstrumento'] ?? null),
                'variable_unit_of_measure' => $r['Variable'] ?? null,
                'types_of_criticality' => $this->mapCriticality($r['Tipo'] ?? null),
                'level_of_criticality' => 'BAJA',
                'emt_value' => $r['Tolerancia'] ?? null,
                'emt_value_decimal' => null,
                'emt_unit' => null,
                'emt_symmetry' => false,
                'file_manual' => $r['Instructivo'] ?? 'N/A',
                'calibration_periodicity_days' => (int) round($r['FrecuenciaCalibracion'] ?? 0),
                'validation_periodicity_days' => (int) round($r['FrecuenciaVerificacion'] ?? 0),
                'maintenance_periodicity_days' => (int) round($r['FrecuenciaMantenimiento'] ?? 0),
                'last_calibration_date' => $this->date($r['FechaUltimaCalibracion'] ?? null),
                'next_calibration_date' => $this->date($r['FechaProximaCalibracion'] ?? null),
                'last_calibration_user' => $r['PersonaCalibro'] ?? null,
                'last_validation_date' => $this->date($r['FechaUltimaVerificacion'] ?? null),
                'next_validation_date' => $this->date($r['FechaProximaVerificacion'] ?? null),
                'last_validation_user' => $r['PersonaVerifico'] ?? null,
                'last_maintenance_date' => $this->date($r['FechaUltimoMantto'] ?? null),
                'next_maintenance_date' => $this->date($r['FechaProximoMantto'] ?? null),
                'last_maintenance_user' => $r['PersonaMantto'] ?? null,
                'is_operational' => ($r['Estado'] ?? null) === 'Activo',
                'observations' => $r['Observaciones'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($out, 100) as $chunk) {
            DB::table('instruments')->insert($chunk);
        }

        $this->command?->info('Instrumentos importados: '.count($out));
    }

    private function mapForm(?string $v): string
    {
        $v = $v !== null ? trim($v) : null;
        $allowed = [
            'Instrumento Electrónico', 'Instrumento Mecánico',
            'Instrumento Simple', 'Reactivo', 'Otro',
        ];

        return in_array($v, $allowed, true) ? $v : 'Otro';
    }

    private function mapCriticality(?string $v): string
    {
        return trim((string) $v) === 'Crítico' ? 'CRITICO' : 'NO_CRITICO';
    }

    private function date(?string $v): ?string
    {
        return $v ? Carbon::parse($v)->toDateString() : null;
    }

    private function readJson(string $file): array
    {
        return json_decode(file_get_contents($this->dir.'/'.$file), true) ?? [];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=InstrumentImportSeederTest`
Expected: PASS (o SKIPPED si no hay JSON local — en la máquina con la extracción debe PASAR).

- [ ] **Step 5: Commit**

```bash
git add database/seeders/InstrumentImportSeeder.php tests/Feature/InstrumentImportSeederTest.php
git commit -m "feat(seeders): import instruments from Access export"
```

---

### Task 3: `InstrumentImportSeeder` — importar eventos históricos

**Files:**
- Modify: `database/seeders/InstrumentImportSeeder.php`
- Modify: `tests/Feature/InstrumentImportSeederTest.php`

**Interfaces:**
- Consumes: métodos `date()`, `readJson()` de Task 2; tabla `instruments` poblada con `code`→`id`.
- Produces: tras `run()`, `instrument_events` contiene 25403 filas; huérfanos y fecha-null omitidos y reportados.

- [ ] **Step 1: Write the failing test (añadir al archivo existente)**

Añadir a `tests/Feature/InstrumentImportSeederTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=InstrumentImportSeederTest`
Expected: FAIL — `instrument_events` count 0 (aún no se importan eventos).

- [ ] **Step 3: Añadir importación de eventos al seeder**

En `run()`, después de `$this->importInstruments();`:

```php
        $this->importEvents();
```

Añadir el método y su config a la clase:

```php
    private function importEvents(): void
    {
        // code => instrument_id
        $ids = DB::table('instruments')->pluck('id', 'code');

        $sources = [
            ['HistoricosCalibraciones-1.json', 'CALIBRACION', [
                'fecha' => 'FECHA_CALIB', 'quien' => 'QUIEN_CALIB',
                'reporte' => 'REPORTE_CALIB', 'resultados' => 'RESULTADOS_CALIB',
                'adecuado' => 'ADECUADO USO_CALIB', 'patron' => null,
            ]],
            ['HistoricosVerificaciones-1.json', 'VALIDACION', [
                'fecha' => 'FECHA_VERIF', 'quien' => 'QUIEN_VERIF',
                'reporte' => 'REPORTE_VERIF', 'resultados' => 'RESULTADOS_VERIF',
                'adecuado' => 'ADECUADO USO_VERIF', 'patron' => 'PATRON USADO_VERIF',
            ]],
            ['HistoricosMantenimiento-1.json', 'MANTENIMIENTO', [
                'fecha' => 'FECHA_MANTTO', 'quien' => 'QUIEN DIO_MANTTO',
                'reporte' => 'REPORTE_MANTTO', 'resultados' => 'OBSERVACIONES_MANTTO',
                'adecuado' => null, 'patron' => null,
            ]],
        ];

        $now = now();
        $orphans = 0;
        $nullDates = 0;

        foreach ($sources as [$file, $type, $m]) {
            $rows = $this->readJson($file);
            $out = [];

            foreach ($rows as $r) {
                $code = $r['Código'] ?? null;
                if ($code === null || ! isset($ids[$code])) {
                    $orphans++;
                    continue;
                }
                $fecha = $this->date($r[$m['fecha']] ?? null);
                if ($fecha === null) {
                    $nullDates++;
                    continue;
                }

                $resultados = $r[$m['resultados']] ?? null;
                if ($m['patron'] !== null && ! empty($r[$m['patron']])) {
                    $resultados = trim(($resultados ?? '')."\nPatrón: ".$r[$m['patron']]);
                }

                $adecuado = $m['adecuado'] === null
                    ? true
                    : strtoupper(trim((string) ($r[$m['adecuado']] ?? ''))) === 'SI';

                $out[] = [
                    'instrument_id' => $ids[$code],
                    'event_type' => $type,
                    'fecha_evento' => $fecha,
                    'responsable' => $r[$m['quien']] ?? null,
                    'reporte' => $r[$m['reporte']] ?? null,
                    'resultados' => $resultados,
                    'adecuado' => $adecuado,
                    'fecha_proxima' => null,
                    'fecha_maxima' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($out, 500) as $chunk) {
                DB::table('instrument_events')->insert($chunk);
            }
            $this->command?->info("Eventos $type importados: ".count($out));
        }

        $this->command?->warn("Eventos huérfanos omitidos: $orphans");
        $this->command?->warn("Eventos con fecha null omitidos: $nullDates");
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=InstrumentImportSeederTest`
Expected: PASS (ambos tests).

- [ ] **Step 5: Commit**

```bash
git add database/seeders/InstrumentImportSeeder.php tests/Feature/InstrumentImportSeederTest.php
git commit -m "feat(seeders): import historical events with orphan/null-date skip"
```

---

### Task 4: Registrar en `DatabaseSeeder`

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Consumes: `InstrumentImportSeeder` de Tasks 2-3.
- Produces: `db:seed` ejecuta la carga completa desde Access en vez de los seeders demo.

- [ ] **Step 1: Editar DatabaseSeeder**

Reemplazar las llamadas a `InstrumentZacSeeder` e `InstrumentEventSeeder` por `InstrumentImportSeeder`:

```php
        $this->call(InstrumentImportSeeder::class);
```

`DatabaseSeeder::run()` queda:

```php
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(InstrumentImportSeeder::class);
    }
```

(Se conservan `InstrumentZacSeeder`, `InstrumentSeeder`, `InstrumentEventSeeder` en el repo, sin llamarse.)

- [ ] **Step 2: Verificar la suite completa**

Run: `php artisan test`
Expected: PASS (los tests nuevos PASAN con los JSON locales, o SKIPPED sin ellos; el resto de la suite sigue verde).

- [ ] **Step 3: Ejecución real (manual, en la máquina con los JSON)**

Run: `php artisan migrate:fresh --seed`
Expected: sin errores; salida muestra "Instrumentos importados: 777", conteos de eventos, y omitidos. Verificar en BD: `instruments`=777, `instrument_events`≈25403.

- [ ] **Step 4: Commit**

```bash
git add database/seeders/DatabaseSeeder.php
git commit -m "feat(seeders): wire InstrumentImportSeeder into DatabaseSeeder"
```

---

## Addendum: Estados y listados de administración (Tasks 5-7)

### Task 5: Columna `status` + mapeo en seeder + scopes
- Migración `2026_09_10_000001_add_status_to_instruments.php`: `status` string nullable indexado.
- `Instrument`: constantes `ESTADO_*`, `ESTADOS_OCULTOS`, `status` en `$fillable`, scopes
  `visibles()` (`whereNotIn('status', ESTADOS_OCULTOS)`) y `status($s)`.
- Seeder: `'status' => $r['Estado'] ?? null`.
- Test (añadido a `InstrumentImportSeederTest`): conteos por estado (382/292/90/13) y
  `Instrument::visibles()->count() === 395`; `status` de code `048012` = `Activo`.

### Task 6: Pantallas de estado (Opción B)
- `InstrumentBajaListScreen` (status=Baja) y `InstrumentFueraServicioListScreen`
  (status=Fuera de Servicio). Tabla con columnas + link Código→Ver y acción Histórico→
  `platform.instruments.events`.
- Rutas `instruments/baja` y `instruments/fuera-de-servicio` **antes** de
  `instruments/{instrument}`.
- `InstrumentListScreen::query` usa `Instrument::visibles()`.

### Task 7: Menú
- Grupo "Administración de Estados" en `PlatformProvider` con ítems Dados de Baja / Fuera de
  Servicio + badges de conteo por `status`.

## Notas de ejecución

- Los tests dependen de `database/mdb_export/*.json` (gitignored). En una máquina sin ellos se marcan **skipped**, no fallan. En CI el seeder no corre por defecto.
- Este seeder es una carga inicial one-shot; el `truncate` lo hace re-ejecutable de forma segura en dev.
