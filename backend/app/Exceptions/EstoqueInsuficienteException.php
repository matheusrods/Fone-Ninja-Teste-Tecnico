<?php

namespace App\Exceptions;

use RuntimeException;

final class EstoqueInsuficienteException extends RuntimeException
{
    public function __construct(string $nomeProduto, int $estoqueDisponivel)
    {
        parent::__construct(
            "Estoque insuficiente para {$nomeProduto}. Disponivel: {$estoqueDisponivel}."
        );
    }
}
