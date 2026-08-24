<?php

namespace Database\Factories;

use App\Enums\TipoEtiqueta;
use App\Models\Etiqueta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Etiqueta>
 */
class EtiquetaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'etiqueta' => fake()->randomElement(TipoEtiqueta::cases()),
        ];
    }
}
