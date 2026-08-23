<?php

namespace App\Models;

use App\Enums\TipoEtiqueta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etiqueta extends Model
{
    /** @use HasFactory<\Database\Factories\EtiquetaFactory> */
    use HasFactory;

    protected $table = 'etiquetas';

    protected $fillable = [
        'etiqueta',
    ];

    protected function casts(): array
    {
        return [
            'etiqueta' => TipoEtiqueta::class,
        ];
    }

    public function tareas(): BelongsToMany
    {
        return $this->belongsToMany(Tarea::class)->withTimestamps();
    }
}
