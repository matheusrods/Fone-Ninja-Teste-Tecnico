<?php

namespace App\Http\Controllers\Api;

use App\DTOs\RegistrarVendaDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendaRequest;
use App\Http\Resources\VendaResource;
use App\Models\Venda;
use App\Services\Contracts\VendaServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendaController extends Controller
{
    public function __construct(private readonly VendaServiceInterface $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Venda::class);

        $perPage = min((int) request()->query('per_page', 20), 1000);

        return VendaResource::collection($this->service->listar($perPage));
    }

    public function store(StoreVendaRequest $request): JsonResponse
    {
        $this->authorize('create', Venda::class);

        $venda = $this->service->registrar(
            RegistrarVendaDTO::fromArray($request->validated())
        );

        return (new VendaResource($venda))
            ->response()
            ->setStatusCode(201);
    }

    public function cancelar(Venda $venda): JsonResponse
    {
        $this->authorize('cancelar', $venda);

        $venda = $this->service->cancelar($venda);

        return (new VendaResource($venda))->response();
    }
}
