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
        // firstOrCreate y no factory(): el entrypoint de Docker corre
        // 'migrate --seed' en cada arranque, y el email es único.
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password'],
        );

        // El orden importa: TareaSeeder necesita los catálogos ya sembrados
        // para resolver prioridad_id y los ids de las etiquetas.
        $this->call([
            PrioridadSeeder::class,
            EtiquetaSeeder::class,
            TareaSeeder::class,
        ]);
    }
}
