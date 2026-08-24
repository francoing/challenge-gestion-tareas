<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // El orden importa: TareaSeeder necesita los catálogos ya sembrados
        // para resolver prioridad_id y los ids de las etiquetas.
        $this->call([
            PrioridadSeeder::class,
            EtiquetaSeeder::class,
            TareaSeeder::class,
        ]);
    }
}
