<?php

namespace App\DTOs;

final class VendaItemDTO
{
    public function __construct(
        public readonly int $produtoId,
        public readonly int $quantidade,
        public readonly float $precoUnitario,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            produtoId: (int) $data['id'],
            quantidade: (int) $data['quantidade'],
            precoUnitario: (float) $data['preco_unitario'],
        );
    }
}
