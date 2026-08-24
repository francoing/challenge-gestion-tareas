<?php

namespace Database\Seeders;

use App\Enums\TipoEtiqueta;
use App\Models\Etiqueta;
use Illuminate\Database\Seeder;

class EtiquetaSeeder extends Seeder
{
    /**
     * Siembra el catálogo a partir del enum, así nunca se desincroniza
     * de los valores que acepta el ENUM de la migración.
     */
    public function run(): void
    {
        foreach (TipoEtiqueta::cases() as $tipo) {
            Etiqueta::firstOrCreate(['etiqueta' => $tipo->value]);
        }
    }
}
