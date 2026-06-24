<?php

namespace App\Services;

use App\DTOs\RegistrarVendaDTO;
use App\DTOs\VendaItemDTO;
use App\Events\VendaCancelada;
use App\Events\VendaRegistrada;
use App\Exceptions\EstoqueInsuficienteException;
use App\Exceptions\VendaJaCanceladaException;
use App\Models\Venda;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Repositories\Contracts\VendaRepositoryInterface;
use App\Services\Contracts\VendaServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class VendaService implements VendaServiceInterface
{
    public function __construct(
        private readonly VendaRepositoryInterface $vendaRepository,
        private readonly ProdutoRepositoryInterface $produtoRepository,
    ) {}

    public function listar(int $perPage = 20): LengthAwarePaginator
    {
        return $this->vendaRepository->listar($perPage);
    }

    public function registrar(RegistrarVendaDTO $dto): Venda
    {
        $venda = DB::transaction(function () use ($dto): Venda {
            $total = 0.0;
            $lucro = 0.0;
            $itensPreparados = [];

            foreach ($dto->itens as $item) {
                $produto = $this->produtoRepository->buscarParaUpdate($item->produtoId);

                if ($produto->estoque < $item->quantidade) {
                    throw new EstoqueInsuficienteException($produto->nome, $produto->estoque);
                }

                $custoUnitario = (float) $produto->custo_medio;
                $subtotal = $item->quantidade * $item->precoUnitario;
                $lucroItem = $item->quantidade * ($item->precoUnitario - $custoUnitario);

                $total += $subtotal;
                $lucro += $lucroItem;
                $itensPreparados[] = compact('produto', 'item', 'custoUnitario', 'subtotal', 'lucroItem');
            }

            $venda = $this->vendaRepository->criar($dto->cliente, round($total, 2), round($lucro, 2));

            foreach ($itensPreparados as $preparado) {
                /** @var VendaItemDTO $item */
                $item = $preparado['item'];
                $produto = $preparado['produto'];

                $venda->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => round($item->precoUnitario, 2),
                    'custo_unitario' => round($preparado['custoUnitario'], 4),
                    'subtotal' => round($preparado['subtotal'], 2),
                    'lucro' => round($preparado['lucroItem'], 2),
                ]);

                $produto->decrement('estoque', $item->quantidade);
            }

            return $venda->load('itens.produto:id,nome');
        });

        event(new VendaRegistrada($venda));

        return $venda;
    }

    public function cancelar(Venda $venda): Venda
    {
        if ($venda->isCancelada()) {
            throw new VendaJaCanceladaException();
        }

        DB::transaction(function () use ($venda): void {
            $venda->load('itens');

            foreach ($venda->itens as $item) {
                /** @var \App\Models\VendaItem $item */
                $produto = $this->produtoRepository->buscarParaUpdateComExcluidos((int) $item->produto_id);
                $produto->increment('estoque', (int) $item->quantidade);
            }

            $venda->update(['status' => \App\Enums\VendaStatus::Cancelada]);
        });

        event(new VendaCancelada($venda->fresh()));

        return $venda->fresh();
    }

    public static function calcularLucroItem(float $preco, float $custo, int $quantidade): float
    {
        return $quantidade * ($preco - $custo);
    }
}
