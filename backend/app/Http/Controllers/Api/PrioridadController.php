<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrioridadResource;
use App\Services\Catalogo\Contracts\CatalogoInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Catálogo de prioridades. Sólo lectura: los valores los fija el ENUM.
 */
class PrioridadController extends Controller
{
    public function __construct(private readonly CatalogoInterface $catalogo) {}

    public function index(): AnonymousResourceCollection
    {
        return PrioridadResource::collection($this->catalogo->prioridades());
    }
}
