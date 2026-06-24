<?php

namespace App\Repositories;

use App\Models\Produto;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use Illuminate\Support\Collection;

final class EloquentProdutoRepository implements ProdutoRepositoryInterface
{
    public function listarOrdenadosPorNome(): Collection
    {
        return Produto::query()->orderBy('nome')->get();
    }

    public function criar(array $dados): Produto
    {
        return Produto::create($dados);
    }

    public function atualizar(Produto $produto, array $dados): Produto
    {
        $produto->update($dados);

        return $produto->fresh();
    }

    public function excluir(Produto $produto): void
    {
        $produto->delete();
    }

    public function buscarParaUpdate(int $id): Produto
    {
        return Produto::query()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function buscarParaUpdateComExcluidos(int $id): Produto
    {
        return Produto::withTrashed()->whereKey($id)->lockForUpdate()->firstOrFail();
    }
}
