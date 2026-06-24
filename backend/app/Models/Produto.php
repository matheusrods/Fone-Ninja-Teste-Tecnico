<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'preco_venda',
        'custo_medio',
        'estoque',
    ];

    protected $casts = [
        'preco_venda' => 'decimal:2',
        'custo_medio' => 'decimal:4',
        'estoque' => 'integer',
    ];

    public function compraItens(): HasMany
    {
        return $this->hasMany(CompraItem::class);
    }

    public function vendaItens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }
}
