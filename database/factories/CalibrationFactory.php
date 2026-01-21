<?php

namespace Database\Factories;

use App\Models\CatalogItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @deprecated This class is deprecated.
 */
class CalibrationFactory extends Factory
{
    public function definition(): array
    {
        $fecha = $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d');

        return [
            // 'catalog_item_id' => CatalogItem::factory(),
            'fecha_calibracion' => $fecha,
            'responsable' => $this->faker->name(),
            'reporte' => 'REP-'.$this->faker->unique()->numerify('#####'),
            'resultados' => 'k=2, ±0.5%',
            'observaciones' => $this->faker->sentence(),
            'adecuado' => true,
            'fecha_proxima' => date('Y-m-d', strtotime($fecha.' +6 months')),
            'fecha_maxima' => date('Y-m-d', strtotime($fecha.' +7 months')),
        ];
    }
}
