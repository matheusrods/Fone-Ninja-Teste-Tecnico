<?php

namespace App\Services\Contracts;

use App\Models\Produto;
use Illuminate\Support\Collection;

interface ProdutoServiceInterface
{
    public function listar(): Collection;

    public function cadastrar(array $dados): Produto;

    public function atualizar(Produto $produto, array $dados): Produto;

    public function excluir(Produto $produto): void;
}
