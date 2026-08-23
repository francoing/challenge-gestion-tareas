<?php

namespace App\Services\Tarea\DTOs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload de entrada para crear o actualizar una tarea.
 *
 * Distingue "campo no enviado" de "campo enviado en null" mediante $presentes,
 * necesario para los PATCH parciales (fecha_vencimiento puede ser null a propósito).
 */
class TareaData
{
    /**
     * @param  array<int, int>|null  $etiquetas  IDs de etiquetas
     * @param  array<int, string>  $presentes  claves realmente enviadas en el request
     */
    private function __construct(
        public readonly ?string $titulo,
        public readonly ?string $descripcion,
        public readonly ?string $estado,
        public readonly ?string $fechaVencimiento,
        public readonly ?int $prioridadId,
        public readonly ?array $etiquetas,
        private readonly array $presentes,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $datos = $request->validated();

        return new self(
            titulo: $datos['titulo'] ?? null,
            descripcion: $datos['descripcion'] ?? null,
            estado: $datos['estado'] ?? null,
            fechaVencimiento: $datos['fecha_vencimiento'] ?? null,
            prioridadId: isset($datos['prioridad_id']) ? (int) $datos['prioridad_id'] : null,
            etiquetas: isset($datos['etiquetas']) ? array_map('intval', $datos['etiquetas']) : null,
            presentes: array_keys($datos),
        );
    }

    /**
     * Atributos listos para create()/update(), limitados a los campos
     * que el cliente realmente envió.
     *
     * @return array<string, mixed>
     */
    public function atributos(): array
    {
        $mapa = [
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'fecha_vencimiento' => $this->fechaVencimiento,
            'prioridad_id' => $this->prioridadId,
        ];

        return array_filter(
            $mapa,
            fn (string $campo): bool => in_array($campo, $this->presentes, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Indica si el request trae etiquetas para sincronizar.
     * Distinto de "trae un array vacío", que significa quitarlas todas.
     */
    public function tieneEtiquetas(): bool
    {
        return in_array('etiquetas', $this->presentes, true);
    }
}
