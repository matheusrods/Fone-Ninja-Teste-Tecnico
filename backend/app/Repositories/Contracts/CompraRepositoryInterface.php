<?php

namespace App\Repositories\Contracts;

use App\Models\Compra;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CompraRepositoryInterface
{
    public function listar(int $perPage = 20): LengthAwarePaginator;

    public function criar(string $fornecedor, float $total): Compra;
}
