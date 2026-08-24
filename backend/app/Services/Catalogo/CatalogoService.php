<?php

namespace App\Services\Catalogo;

use App\Models\Etiqueta;
use App\Models\Prioridad;
use App\Services\Catalogo\Contracts\CatalogoInterface;
use Illuminate\Database\Eloquent\Collection;

class CatalogoService implements CatalogoInterface
{
    /** Ordenadas por id: refleja el orden en que las sembró el seeder. */
    public function prioridades(): Collection
    {
        return Prioridad::orderBy('id')->get();
    }

    public function etiquetas(): Collection
    {
        return Etiqueta::orderBy('id')->get();
    }
}
