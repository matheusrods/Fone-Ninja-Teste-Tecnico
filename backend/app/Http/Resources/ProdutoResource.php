<?php

namespace App\Http\Resources;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Produto */
class ProdutoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'custo_medio' => (float) $this->custo_medio,
            'preco_venda' => (float) $this->preco_venda,
            'estoque' => $this->estoque,
        ];
    }
}
