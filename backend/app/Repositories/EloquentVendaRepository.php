<?php

namespace App\Repositories;

use App\Enums\VendaStatus;
use App\Models\Venda;
use App\Repositories\Contracts\VendaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentVendaRepository implements VendaRepositoryInterface
{
    public function listar(int $perPage = 20): LengthAwarePaginator
    {
        return Venda::query()
            ->with('itens.produto:id,nome')
            ->latest()
            ->paginate($perPage);
    }

    public function criar(string $cliente, float $total, float $lucro): Venda
    {
        return Venda::create([
            'cliente' => $cliente,
            'total' => $total,
            'lucro' => $lucro,
            'status' => VendaStatus::Ativa,
        ]);
    }
}
