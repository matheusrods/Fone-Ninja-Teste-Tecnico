<?php

namespace App\Repositories\Contracts;

use App\Models\Produto;
use Illuminate\Support\Collection;

interface ProdutoRepositoryInterface
{
    public function listarOrdenadosPorNome(): Collection;

    public function criar(array $dados): Produto;

    public function atualizar(Produto $produto, array $dados): Produto;

    public function excluir(Produto $produto): void;

    /** Busca com lock de escrita para uso dentro de transacoes. */
    public function buscarParaUpdate(int $id): Produto;

    /** Busca incluindo produtos excluidos (soft deleted) para restaurar estoque em cancelamentos. */
    public function buscarParaUpdateComExcluidos(int $id): Produto;
}
