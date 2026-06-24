<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use App\Services\Contracts\ProdutoServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProdutoController extends Controller
{
    public function __construct(private readonly ProdutoServiceInterface $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Produto::class);

        return ProdutoResource::collection($this->service->listar());
    }

    public function store(StoreProdutoRequest $request): JsonResponse
    {
        $this->authorize('create', Produto::class);

        $produto = $this->service->cadastrar($request->validated());

        return (new ProdutoResource($produto))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProdutoRequest $request, Produto $produto): JsonResponse
    {
        $this->authorize('update', $produto);

        $produto = $this->service->atualizar($produto, $request->validated());

        return (new ProdutoResource($produto))
            ->response();
    }

    public function destroy(Produto $produto): Response
    {
        $this->authorize('delete', $produto);

        $this->service->excluir($produto);

        return response()->noContent();
    }
}
