<?php

namespace App\Http\Resources;

use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Venda */
class VendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cliente' => $this->cliente,
            'total' => (float) $this->total,
            'lucro' => (float) $this->lucro,
            'status' => $this->status->value,
            'created_at' => $this->created_at,
            // @phpstan-ignore-next-line
            'itens' => $this->whenLoaded('itens', fn (): array => $this->itens
                ->map(fn (VendaItem $item): array => [
                    'id' => $item->id,
                    'produto' => ['id' => $item->produto->id, 'nome' => $item->produto->nome],
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => (float) $item->preco_unitario,
                    'custo_unitario' => (float) $item->custo_unitario,
                    'subtotal' => (float) $item->subtotal,
                    'lucro' => (float) $item->lucro,
                ])
                ->all()
            ),
        ];
    }
}
