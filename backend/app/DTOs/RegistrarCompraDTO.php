<?php

namespace App\DTOs;

final class RegistrarCompraDTO
{
    /** @param CompraItemDTO[] $itens */
    public function __construct(
        public readonly string $fornecedor,
        public readonly array $itens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            fornecedor: $data['fornecedor'],
            itens: array_map(fn (array $i) => CompraItemDTO::fromArray($i), $data['produtos']),
        );
    }
}
