<?php

namespace App\Listeners;

use App\Events\CompraRegistrada;
use App\Events\VendaCancelada;
use App\Events\VendaRegistrada;
use Illuminate\Support\Facades\Log;

final class LogMovimentacaoEstoque
{
    public function handleVenda(VendaRegistrada $event): void
    {
        Log::info('Venda registrada', [
            'id' => $event->venda->id,
            'cliente' => $event->venda->cliente,
            'total' => $event->venda->total,
            'lucro' => $event->venda->lucro,
            'itens' => $event->venda->itens->count(),
        ]);
    }

    public function handleCompra(CompraRegistrada $event): void
    {
        Log::info('Compra registrada', [
            'id' => $event->compra->id,
            'fornecedor' => $event->compra->fornecedor,
            'total' => $event->compra->total,
            'itens' => $event->compra->itens->count(),
        ]);
    }

    public function handleVendaCancelada(VendaCancelada $event): void
    {
        Log::info('Venda cancelada', [
            'id' => $event->venda->id,
            'cliente' => $event->venda->cliente,
            'total' => $event->venda->total,
        ]);
    }
}
