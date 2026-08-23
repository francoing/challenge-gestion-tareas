<?php

namespace App\Models;

use App\Enums\EstadoTarea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tarea extends Model
{
    /** @use HasFactory<\Database\Factories\TareaFactory> */
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'fecha_vencimiento',
        'prioridad_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'estado' => EstadoTarea::class,
        ];
    }

    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(Prioridad::class);
    }

    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(Etiqueta::class)->withTimestamps();
    }

    /**
     * Filtra por estado (bonus del challenge).
     */
    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        return $query->when($estado, fn (Builder $q) => $q->where('estado', $estado));
    }

    /**
     * Filtra por prioridad.
     */
    public function scopePrioridad(Builder $query, int|string|null $prioridadId): Builder
    {
        return $query->when($prioridadId, fn (Builder $q) => $q->where('prioridad_id', $prioridadId));
    }

    /**
     * Filtra por rango de fecha de vencimiento (bonus del challenge).
     */
    public function scopeVenceEntre(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        return $query
            ->when($desde, fn (Builder $q) => $q->whereDate('fecha_vencimiento', '>=', $desde))
            ->when($hasta, fn (Builder $q) => $q->whereDate('fecha_vencimiento', '<=', $hasta));
    }

    /**
     * Filtra por etiquetas asignadas.
     *
     * @param  array<int, int|string>|null  $etiquetaIds
     */
    public function scopeConEtiquetas(Builder $query, ?array $etiquetaIds): Builder
    {
        return $query->when(
            $etiquetaIds,
            fn (Builder $q) => $q->whereHas(
                'etiquetas',
                fn (Builder $e) => $e->whereIn('etiquetas.id', $etiquetaIds)
            )
        );
    }

    /**
     * Busca por titulo o descripcion.
     */
    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        return $query->when($termino, function (Builder $q) use ($termino) {
            $q->where(function (Builder $sub) use ($termino) {
                $sub->where('titulo', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%");
            });
        });
    }
}
