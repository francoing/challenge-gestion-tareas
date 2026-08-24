<?php

namespace App\Services\Catalogo\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Catálogos de sólo lectura que alimentan los selects del formulario.
 *
 * Sus valores están cerrados por el ENUM de las migraciones, por eso no
 * existen operaciones de escritura.
 */
interface CatalogoInterface
{
    /** @return Collection<int, \App\Models\Prioridad> */
    public function prioridades(): Collection;

    /** @return Collection<int, \App\Models\Etiqueta> */
    public function etiquetas(): Collection;
}
