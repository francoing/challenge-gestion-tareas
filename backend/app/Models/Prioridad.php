<?php

namespace App\Models;

use App\Enums\NivelPrioridad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prioridad extends Model
{
    /** @use HasFactory<\Database\Factories\PrioridadFactory> */
    use HasFactory;

    protected $table = 'prioridades';

    protected $fillable = [
        'prioridad',
    ];

    protected function casts(): array
    {
        return [
            'prioridad' => NivelPrioridad::class,
        ];
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }
}
