<?php

namespace App\Http\Controllers\Api;

use App\DTOs\RegistrarCompraDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Resources\CompraResource;
use App\Models\Compra;
use App\Services\Contracts\CompraServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompraController extends Controller
{
    public function __construct(private readonly CompraServiceInterface $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Compra::class);

        $perPage = min((int) request()->query('per_page', 20), 1000);

        return CompraResource::collection($this->service->listar($perPage));
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $this->authorize('create', Compra::class);

        $compra = $this->service->registrar(
            RegistrarCompraDTO::fromArray($request->validated())
        );

        return (new CompraResource($compra))
            ->response()
            ->setStatusCode(201);
    }
}
