<?php

namespace App\Http\Resources;

use App\Models\Compra;
use App\Models\CompraItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Compra */
class CompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fornecedor' => $this->fornecedor,
            'total' => (float) $this->total,
            'created_at' => $this->created_at,
            // @phpstan-ignore-next-line
            'itens' => $this->whenLoaded('itens', fn (): array => $this->itens
                ->map(fn (CompraItem $item): array => [
                    'id' => $item->id,
                    'produto' => ['id' => $item->produto->id, 'nome' => $item->produto->nome],
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => (float) $item->preco_unitario,
                    'subtotal' => (float) $item->subtotal,
                ])
                ->all()
            ),
        ];
    }
}
