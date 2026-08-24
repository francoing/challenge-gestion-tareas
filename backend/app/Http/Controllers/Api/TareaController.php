<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexTareaRequest;
use App\Http\Requests\StoreTareaRequest;
use App\Http\Requests\UpdateTareaRequest;
use App\Http\Resources\TareaResource;
use App\Services\Tarea\Contracts\TareaInterface;
use App\Services\Tarea\DTOs\FiltroTareaData;
use App\Services\Tarea\DTOs\TareaData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TareaController extends Controller
{
    /** Se tipa la interfaz, no TareaService: el contenedor resuelve el bind. */
    public function __construct(private readonly TareaInterface $tareas) {}

    public function index(IndexTareaRequest $request): AnonymousResourceCollection
    {
        return TareaResource::collection(
            $this->tareas->listar(FiltroTareaData::fromRequest($request))
        );
    }

    public function store(StoreTareaRequest $request): JsonResponse
    {
        $tarea = $this->tareas->crear(TareaData::fromRequest($request));

        return TareaResource::make($tarea)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): TareaResource
    {
        return TareaResource::make($this->tareas->verDetalle($id));
    }

    public function update(UpdateTareaRequest $request, int $id): TareaResource
    {
        return TareaResource::make(
            $this->tareas->actualizar($id, TareaData::fromRequest($request))
        );
    }

    public function destroy(int $id): Response
    {
        $this->tareas->eliminar($id);

        return response()->noContent();
    }
}
