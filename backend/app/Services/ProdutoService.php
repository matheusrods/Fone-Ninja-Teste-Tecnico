<?php

namespace App\Services;

use App\Models\Produto;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Services\Contracts\ProdutoServiceInterface;
use Illuminate\Support\Collection;

final class ProdutoService implements ProdutoServiceInterface
{
    public function __construct(private readonly ProdutoRepositoryInterface $repository) {}

    public function listar(): Collection
    {
        return $this->repository->listarOrdenadosPorNome();
    }

    public function cadastrar(array $dados): Produto
    {
        return $this->repository->criar([
            'nome' => $dados['nome'],
            'preco_venda' => $dados['preco_venda'],
            'custo_medio' => 0,
            'estoque' => 0,
        ]);
    }

    public function atualizar(Produto $produto, array $dados): Produto
    {
        return $this->repository->atualizar($produto, $dados);
    }

    public function excluir(Produto $produto): void
    {
        $this->repository->excluir($produto);
    }
}
