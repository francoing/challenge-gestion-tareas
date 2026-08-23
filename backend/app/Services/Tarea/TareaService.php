<?php

namespace App\Services\Tarea;

use App\Models\Tarea;

class TareaService
{
    /**
     * Relaciones que toda tarea expone en la API.
     *
     * Se centralizan acá para que el eager loading sea consistente en todos
     * los métodos: si el Resource espera 'prioridad' y el service no la carga,
     * whenLoaded() la omite y el cliente recibe un JSON incompleto.
     *
     * @var array<int, string>
     */
    private const RELACIONES = ['prioridad', 'etiquetas'];

    /**
     * Devuelve una tarea con sus relaciones cargadas.
     *
     * No se atrapa la excepción a propósito: findOrFail lanza
     * ModelNotFoundException y el handler global la traduce a un 404 JSON,
     * de modo que el controller no necesita try/catch.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function verDetalle(int $id): Tarea
    {
        return Tarea::with(self::RELACIONES)->findOrFail($id);
    }
}
