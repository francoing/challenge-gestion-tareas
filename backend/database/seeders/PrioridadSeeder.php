<?php

namespace Database\Seeders;

use App\Enums\NivelPrioridad;
use App\Models\Prioridad;
use Illuminate\Database\Seeder;

class PrioridadSeeder extends Seeder
{
    /**
     * Siembra el catálogo a partir del enum, así nunca se desincroniza
     * de los valores que acepta el ENUM de la migración.
     */
    public function run(): void
    {
        foreach (NivelPrioridad::cases() as $nivel) {
            Prioridad::firstOrCreate(['prioridad' => $nivel->value]);
        }
    }
}
