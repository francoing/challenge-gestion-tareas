<?php

namespace App\Http\Requests;

use App\Enums\EstadoTarea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Valida los query params del listado. Todos opcionales.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'estado' => ['sometimes', 'nullable', Rule::enum(EstadoTarea::class)],
            'prioridad_id' => ['sometimes', 'nullable', 'integer', 'exists:prioridades,id'],
            'vence_desde' => ['sometimes', 'nullable', 'date'],
            'vence_hasta' => ['sometimes', 'nullable', 'date', 'after_or_equal:vence_desde'],
            'buscar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estado.enum' => 'El estado debe ser pendiente, en_progreso o completada.',
            'prioridad_id.exists' => 'La prioridad seleccionada no existe.',
            'vence_desde.date' => 'La fecha desde no es una fecha válida.',
            'vence_hasta.date' => 'La fecha hasta no es una fecha válida.',
            'vence_hasta.after_or_equal' => 'La fecha hasta no puede ser anterior a la fecha desde.',
            'buscar.max' => 'La búsqueda no puede superar los :max caracteres.',
            'per_page.max' => 'No se pueden pedir más de :max tareas por página.',
        ];
    }
}
