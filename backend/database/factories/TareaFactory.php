<?php

namespace Database\Factories;

use App\Enums\EstadoTarea;
use App\Models\Prioridad;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tarea>
 */
class TareaFactory extends Factory
{
    /**
     * Datos aleatorios para los tests. Los datos de la demo están en TareaSeeder.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'estado' => fake()->randomElement(EstadoTarea::cases()),
            // 1 de cada 5 sin fecha, para ejercitar el caso nullable.
            'fecha_vencimiento' => fake()->optional(0.8)->dateTimeBetween('-1 month', '+2 months'),
            // Reusa una prioridad existente; sólo crea una si el catálogo está vacío.
            'prioridad_id' => Prioridad::inRandomOrder()->value('id') ?? Prioridad::factory(),
        ];
    }

    public function pendiente(): static
    {
        return $this->state(['estado' => EstadoTarea::PENDIENTE]);
    }

    public function vencida(): static
    {
        return $this->state([
            'fecha_vencimiento' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }
}
