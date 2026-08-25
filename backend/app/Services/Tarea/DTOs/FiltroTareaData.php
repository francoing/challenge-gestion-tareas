<?php

namespace App\Services\Tarea\DTOs;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtros del listado de tareas, recibidos como query params.
 *
 * A diferencia de TareaData, acá "no enviado" y "null" significan lo mismo:
 * no filtrar por ese campo. Por eso no hace falta rastrear qué claves vinieron.
 */
class FiltroTareaData
{
    /** Tamaño de página por defecto cuando el cliente no lo especifica. */
    public const POR_PAGINA = 5;

    private function __construct(
        public readonly ?string $estado,
        public readonly ?int $prioridadId,
        public readonly ?string $venceDesde,
        public readonly ?string $venceHasta,
        public readonly ?string $buscar,
        public readonly int $porPagina,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $datos = $request->validated();

        return new self(
            estado: $datos['estado'] ?? null,
            prioridadId: isset($datos['prioridad_id']) ? (int) $datos['prioridad_id'] : null,
            venceDesde: $datos['vence_desde'] ?? null,
            venceHasta: $datos['vence_hasta'] ?? null,
            buscar: $datos['buscar'] ?? null,
            porPagina: isset($datos['per_page']) ? (int) $datos['per_page'] : self::POR_PAGINA,
        );
    }
}
