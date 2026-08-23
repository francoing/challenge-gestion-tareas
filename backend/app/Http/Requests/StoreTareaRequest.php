<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTareaRequest extends FormRequest
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
     * El estado no se acepta al crear: toda tarea nace como 'pendiente'
     * (default de la migración) y sólo cambia vía UpdateTareaRequest.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'prioridad_id' => ['required', 'integer', 'exists:prioridades,id'],
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
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es una fecha válida.',
            'prioridad_id.required' => 'Debe seleccionar una prioridad.',
            'prioridad_id.exists' => 'La prioridad seleccionada no existe.',
            'etiquetas.array' => 'Las etiquetas deben enviarse como una lista.',
            'etiquetas.*.distinct' => 'No se puede repetir la misma etiqueta.',
            'etiquetas.*.exists' => 'Alguna de las etiquetas seleccionadas no existe.',
        ];
    }
}
