<?php

namespace App\Models;

use App\Enums\VendaStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'cliente',
        'total',
        'lucro',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'lucro' => 'decimal:2',
        'status' => VendaStatus::class,
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    public function isCancelada(): bool
    {
        return $this->status === VendaStatus::Cancelada;
    }
}
