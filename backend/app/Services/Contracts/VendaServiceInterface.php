<?php

namespace App\Services\Contracts;

use App\DTOs\RegistrarVendaDTO;
use App\Models\Venda;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VendaServiceInterface
{
    public function listar(int $perPage = 20): LengthAwarePaginator;

    public function registrar(RegistrarVendaDTO $dto): Venda;

    public function cancelar(Venda $venda): Venda;
}
