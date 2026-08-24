<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EtiquetaResource;
use App\Services\Catalogo\Contracts\CatalogoInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Catálogo de etiquetas. Sólo lectura: los valores los fija el ENUM.
 */
class EtiquetaController extends Controller
{
    public function __construct(private readonly CatalogoInterface $catalogo) {}

    public function index(): AnonymousResourceCollection
    {
        return EtiquetaResource::collection($this->catalogo->etiquetas());
    }
}
