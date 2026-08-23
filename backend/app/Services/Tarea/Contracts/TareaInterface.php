<?php

namespace App\Services\Tarea\Contracts;

use App\Models\Tarea;
use App\Services\Tarea\DTOs\FiltroTareaData;
use App\Services\Tarea\DTOs\TareaData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contrato del servicio de tareas.
 *
 * El controller tipa esta interfaz y el contenedor inyecta la implementación
 * concreta (ver el bind en AppServiceProvider), de modo que la capa HTTP
 * nunca depende de TareaService.
 *
 * Los métodos devuelven modelos de dominio, no arrays: la serialización es
 * responsabilidad de los API Resources.
 */
interface TareaInterface
{
    /**
     * Lista tareas paginadas aplicando los filtros recibidos.
     */
    public function listar(FiltroTareaData $filtros): LengthAwarePaginator;

    /**
     * Devuelve una tarea con sus relaciones cargadas.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException si no existe
     */
    public function verDetalle(int $id): Tarea;

    public function crear(TareaData $datos): Tarea;

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException si no existe
     */
    public function actualizar(int $id, TareaData $datos): Tarea;

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException si no existe
     */
    public function eliminar(int $id): void;
}
