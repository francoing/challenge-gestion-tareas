<?php

namespace App\Models;

use App\Enums\EstadoTarea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tarea extends Model
{
    /** @use HasFactory<\Database\Factories\TareaFactory> */
    use HasFactory;

    protected $table='tareas';

    protected $fillable =[
        'titulo',
        'descripcion',
        'estado',
        'fecha_vencimiento',
        'prioridad_id'
    ];

    protected function casts():array
    {
        return[
            'fecha_vencimiento'=>'date',
            'estado'=>EstadoTarea::class,
        ];
    }

    public function etiquetas()
    {
        return $this->belongsToMany(Etiqueta::class)->withTimestamps();
    } 

    

    public function prioridad()
    {
        return $this->belongsTo(Prioridad::class);
    } 


}
