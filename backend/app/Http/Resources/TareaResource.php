<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Tarea
 */
class TareaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Las relaciones usan whenLoaded() para no disparar consultas N+1:
     * si el controller no las cargó con with(), la clave no aparece.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado?->value,
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'prioridad_id' => $this->prioridad_id,
            'prioridad' => new PrioridadResource($this->whenLoaded('prioridad')),
            'etiquetas' => EtiquetaResource::collection($this->whenLoaded('etiquetas')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
