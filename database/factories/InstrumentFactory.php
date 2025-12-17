<?php

namespace Database\Factories;

use App\Models\Instrument;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstrumentFactory extends Factory
{
    protected $model = Instrument::class;

    public function definition(): array
    {
        // 🔧 Fechas coherentes (última → próxima)
        $lastCalibration = $this->faker->dateTimeBetween('-2 years', 'now');
        $nextCalibration = $this->faker->dateTimeBetween($lastCalibration, '+1 year');

        $lastValidation = $this->faker->optional(0.7)->dateTimeBetween('-2 years', 'now');
        $nextValidation = $lastValidation
            ? $this->faker->dateTimeBetween($lastValidation, '+1 year')
            : null;

        $lastMaintenance = $this->faker->optional(0.8)->dateTimeBetween('-2 years', 'now');
        $nextMaintenance = $lastMaintenance
            ? $this->faker->dateTimeBetween($lastMaintenance, '+1 year')
            : null;

        return [
            // 📋 Información general
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['INSTRUMENTO', 'PLANO']),
            'department' => $this->faker->randomElement([
                'Producción',
                'Calidad',
                'Laboratorio',
                'Mantenimiento',
                'Empaque',
                'Desarrollo',
            ]),
            'location' => $this->faker->randomElement([
                'Planta A', 'Planta B', 'Área 3', 'Zona de Control', 'Almacén',
            ]),
            'form' => $this->faker->randomElement(['Instrumento Electronico', 'Instrumento Simple']),

            // ⚗️ Variables y mediciones
            'Variable Unidad De Medida' => $this->faker->randomElement(['Concentración', 'Flujo', 'Humedad', 'Indice de Refracción', 'KVA', 'MVP', 'N/A', 'Peso', 'Presión', 'Temperatura', 'Transmitancia', 'pH']),
            'equipo' => strtoupper($this->faker->randomElement([
                'TERMOPAR', 'BALANZA', 'MANÓMETRO', 'HIGRÓMETRO', 'MULTÍMETRO', 'TRANSMISOR', 'SENSOR',
            ])),
            'brand' => $this->faker->randomElement([
                'Fluke', 'Omega', 'Siemens', 'Honeywell', 'Yokogawa', 'Testo', 'ABB', 'Endress+Hauser',
            ]),
            'model' => strtoupper($this->faker->bothify('M-###-??')),
            'code' => strtoupper($this->faker->unique()->bothify('INS-#####')),

            // 📏 Exactitud y especificaciones
            'emt_value' => $this->faker->randomFloat(2, 0.01, 10.00),
            'emt_value_decimal' => $this->faker->randomFloat(4, 0.001, 99.9999),
            'emt_unit' => $this->faker->randomElement(['°C', 'bar', 'kg', 'L/min', '%RH', 'V', 'A']),
            'emt_symmetry' => $this->faker->boolean(70),

            // 📘 Manual / documentación
            'file_manual' => 'N/A',

            // ⚠️ Criticidad
            'types_of_criticality' => $this->faker->randomElement(['NO_CRITICO', 'CRITICO']),
            'level_of_criticality' => $this->faker->randomElement(['BAJA', 'MEDIA', 'ALTA']),

            // 📅 Snapshot de calibración
            'last_calibration_date' => $lastCalibration,
            'last_calibration_user' => $this->faker->name(),
            'next_calibration_date' => $nextCalibration,

            // 📅 Snapshot de validación
            'last_validation_date' => $lastValidation,
            'last_validation_user' => $lastValidation ? $this->faker->name() : null,
            'next_validation_date' => $nextValidation,

            // 📅 Snapshot de mantenimiento
            'last_maintenance_date' => $lastMaintenance,
            'last_maintenance_user' => $lastMaintenance ? $this->faker->name() : null,
            'next_maintenance_date' => $nextMaintenance,

            // ⚙️ Estado
            'is_operational' => $this->faker->boolean(85),
            'observations' => $this->faker->optional(0.7)->sentence(8),
        ];
    }
}
