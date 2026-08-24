<?php

namespace App\Services\Tarea;

use App\Models\Tarea;
use App\Services\Tarea\Contracts\TareaInterface;
use App\Services\Tarea\DTOs\FiltroTareaData;
use App\Services\Tarea\DTOs\TareaData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TareaService implements TareaInterface
{
    /** @var array<int, string> Relaciones que el Resource espera cargadas. */
    private const RELACIONES = ['prioridad', 'etiquetas'];

    /**
     * Lista tareas paginadas. Cada when() se salta solo si su filtro es null.
     */
    public function listar(FiltroTareaData $filtros): LengthAwarePaginator
    {
        return Tarea::with(self::RELACIONES)
            ->when($filtros->estado, fn (Builder $q, string $estado) => $q->where('estado', $estado))
            ->when($filtros->prioridadId, fn (Builder $q, int $id) => $q->where('prioridad_id', $id))
            ->when($filtros->venceDesde, fn (Builder $q, string $desde) => $q->whereDate('fecha_vencimiento', '>=', $desde))
            ->when($filtros->venceHasta, fn (Builder $q, string $hasta) => $q->whereDate('fecha_vencimiento', '<=', $hasta))
            ->when($filtros->buscar, $this->filtrarPorTexto(...))
            ->orderByRaw('fecha_vencimiento IS NULL')  // las sin fecha, al final
            ->orderBy('fecha_vencimiento')
            ->orderByDesc('id')
            ->paginate($filtros->porPagina)
            ->withQueryString();
    }

    /**
     * La excepción no se atrapa: el handler global la convierte en 404 JSON.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function verDetalle(int $id): Tarea
    {
        return Tarea::with(self::RELACIONES)->findOrFail($id);
    }

    /**
     * En transacción: insertar la tarea y sincronizar etiquetas deben ser atómicos.
     * Se relee con verDetalle() porque el DEFAULT de 'estado' lo aplica MySQL.
     */
    public function crear(TareaData $datos): Tarea
    {
        $tarea = DB::transaction(function () use ($datos): Tarea {
            $tarea = Tarea::create($datos->atributos());

            if ($datos->tieneEtiquetas()) {
                $tarea->etiquetas()->sync($datos->etiquetas);
            }

            return $tarea;
        });

        return $this->verDetalle($tarea->id);
    }

    /**
     * Actualización parcial: atributos() trae sólo los campos enviados.
     * En etiquetas, clave ausente = no tocar; array vacío = quitar todas.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function actualizar(int $id, TareaData $datos): Tarea
    {
        DB::transaction(function () use ($id, $datos): void {
            $tarea = Tarea::findOrFail($id);
            $tarea->update($datos->atributos());

            if ($datos->tieneEtiquetas()) {
                $tarea->etiquetas()->sync($datos->etiquetas);
            }
        });

        return $this->verDetalle($id);
    }

    /**
     * Sin eager loading: traería relaciones para descartarlas.
     * El pivote lo limpia la base por cascadeOnDelete().
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function eliminar(int $id): void
    {
        Tarea::findOrFail($id)->delete();
    }

    /**
     * El closure agrupa el OR entre paréntesis; sin él se mezclaría
     * con los demás filtros y ampliaría el resultado.
     */
    private function filtrarPorTexto(Builder $query, string $termino): void
    {
        $query->where(function (Builder $sub) use ($termino): void {
            $sub->where('titulo', 'like', "%{$termino}%")
                ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }
}
