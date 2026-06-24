<?php

namespace App\DTOs;

final class RegistrarVendaDTO
{
    /** @param VendaItemDTO[] $itens */
    public function __construct(
        public readonly string $cliente,
        public readonly array $itens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cliente: $data['cliente'],
            itens: array_map(fn (array $i) => VendaItemDTO::fromArray($i), $data['produtos']),
        );
    }
}
