<?php

namespace App\Repositories;

use App\Models\Compra;
use App\Repositories\Contracts\CompraRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentCompraRepository implements CompraRepositoryInterface
{
    public function listar(int $perPage = 20): LengthAwarePaginator
    {
        return Compra::query()
            ->with('itens.produto:id,nome')
            ->latest()
            ->paginate($perPage);
    }

    public function criar(string $fornecedor, float $total): Compra
    {
        return Compra::create([
            'fornecedor' => $fornecedor,
            'total' => $total,
        ]);
    }
}
