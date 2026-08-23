<?php

namespace App\Http\Requests;

use App\Enums\EstadoTarea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTareaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Todas las reglas usan 'sometimes' para permitir actualizaciones
     * parciales (por ejemplo, cambiar sólo el estado desde el listado).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'required', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'required', 'string'],
            'estado' => ['sometimes', 'required', Rule::enum(EstadoTarea::class)],
            'fecha_vencimiento' => ['sometimes', 'nullable', 'date'],
            'prioridad_id' => ['sometimes', 'required', 'integer', 'exists:prioridades,id'],
            'etiquetas' => ['sometimes', 'array'],
            'etiquetas.*' => ['integer', 'distinct', 'exists:etiquetas,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede superar los :max caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.enum' => 'El estado debe ser pendiente, en_progreso o completada.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es una fecha válida.',
            'prioridad_id.required' => 'Debe seleccionar una prioridad.',
            'prioridad_id.exists' => 'La prioridad seleccionada no existe.',
            'etiquetas.array' => 'Las etiquetas deben enviarse como una lista.',
            'etiquetas.*.distinct' => 'No se puede repetir la misma etiqueta.',
            'etiquetas.*.exists' => 'Alguna de las etiquetas seleccionadas no existe.',
        ];
    }
}
