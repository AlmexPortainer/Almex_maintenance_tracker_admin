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
        $this->importEvents();
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
                'status' => $r['Estado'] ?? null,
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
