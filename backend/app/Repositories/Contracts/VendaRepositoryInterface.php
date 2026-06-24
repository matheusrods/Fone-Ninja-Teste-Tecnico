<?php

namespace App\Repositories\Contracts;

use App\Models\Venda;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VendaRepositoryInterface
{
    public function listar(int $perPage = 20): LengthAwarePaginator;

    public function criar(string $cliente, float $total, float $lucro): Venda;
}
