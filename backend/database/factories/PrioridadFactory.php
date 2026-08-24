<?php

namespace Database\Factories;

use App\Enums\NivelPrioridad;
use App\Models\Prioridad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prioridad>
 */
class PrioridadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prioridad' => fake()->randomElement(NivelPrioridad::cases()),
        ];
    }
}
