<?php

namespace App\Services\Contracts;

use App\DTOs\RegistrarCompraDTO;
use App\Models\Compra;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CompraServiceInterface
{
    public function listar(int $perPage = 20): LengthAwarePaginator;

    public function registrar(RegistrarCompraDTO $dto): Compra;
}
