<?php

namespace App\Services;

use App\DTOs\CompraItemDTO;
use App\DTOs\RegistrarCompraDTO;
use App\Events\CompraRegistrada;
use App\Models\Compra;
use App\Repositories\Contracts\CompraRepositoryInterface;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Services\Contracts\CompraServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class CompraService implements CompraServiceInterface
{
    public function __construct(
        private readonly CompraRepositoryInterface $compraRepository,
        private readonly ProdutoRepositoryInterface $produtoRepository,
    ) {}

    public function listar(int $perPage = 20): LengthAwarePaginator
    {
        return $this->compraRepository->listar($perPage);
    }

    public function registrar(RegistrarCompraDTO $dto): Compra
    {
        $compra = DB::transaction(function () use ($dto): Compra {
            $total = array_sum(array_map(
                fn (CompraItemDTO $i) => $i->quantidade * $i->precoUnitario,
                $dto->itens,
            ));

            $compra = $this->compraRepository->criar($dto->fornecedor, round($total, 2));

            foreach ($dto->itens as $item) {
                $produto = $this->produtoRepository->buscarParaUpdate($item->produtoId);

                $novoEstoque = $produto->estoque + $item->quantidade;
                $novoCustoMedio = self::calcularNovoCustoMedio(
                    $produto->estoque,
                    (float) $produto->custo_medio,
                    $item->quantidade,
                    $item->precoUnitario,
                );

                $compra->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->precoUnitario,
                    'subtotal' => round($item->quantidade * $item->precoUnitario, 2),
                ]);

                $produto->update([
                    'estoque' => $novoEstoque,
                    'custo_medio' => round($novoCustoMedio, 4),
                ]);
            }

            return $compra->load('itens.produto:id,nome');
        });

        event(new CompraRegistrada($compra));

        return $compra;
    }

    public static function calcularNovoCustoMedio(
        int $estoqueAtual,
        float $custoAtual,
        int $qtdComprada,
        float $precoUnitario,
    ): float {
        $novoEstoque = $estoqueAtual + $qtdComprada;

        return (($estoqueAtual * $custoAtual) + ($qtdComprada * $precoUnitario)) / $novoEstoque;
    }
}
