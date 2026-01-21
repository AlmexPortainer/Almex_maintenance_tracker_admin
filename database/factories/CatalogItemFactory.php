<?php

namespace Database\Factories;

use App\Enums\ItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => $this->faker->unique()->numerify('########'),
            'tipo_item' => $this->faker->randomElement([ItemType::INSTRUMENTO, ItemType::PLANO]),
            'equipo' => strtoupper($this->faker->bothify('FT-????')),
            'marca' => $this->faker->company(),
            'modelo' => $this->faker->bothify('####????'),
            'requiere_calibracion' => $this->faker->randomElement([true, false]),
            'tipo' => $this->faker->randomElement(['NO_CRITICO', 'CRITICO']),
            'estado' => $this->faker->randomElement(['BAJA', 'ALTA']),
            'emt_valor' => rand(0.0001, 999.0000),
            'emt_unidad' => 'psi',
            'emt_simetrico' => true,
            'ubicacion' => 'Caldera No.'.rand(1, 10),
            'forma' => 'Instrumento Electrónico',
        ];
    }
}
