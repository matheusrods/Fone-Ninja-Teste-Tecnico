<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Compra;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrCreate garante idempotencia: reexecutar o seed nunca
        // bloqueia o login, mesmo em volumes com dados parciais
        User::updateOrCreate(
            ['email' => 'admin@foneninja.com'],
            ['name' => 'Administrador', 'password' => 'password', 'role' => Role::Admin]
        );
        User::updateOrCreate(
            ['email' => 'vendedor@foneninja.com'],
            ['name' => 'Vendedor', 'password' => 'password', 'role' => Role::Vendedor]
        );
        User::updateOrCreate(
            ['email' => 'comprador@foneninja.com'],
            ['name' => 'Comprador', 'password' => 'password', 'role' => Role::Comprador]
        );

        if (Produto::count() > 0) {
            return;
        }

        $fone = Produto::create(['nome' => 'Fone Bluetooth Pro', 'preco_venda' => 299.90, 'custo_medio' => 0, 'estoque' => 0]);
        $cabo = Produto::create(['nome' => 'Cabo USB-C 2m', 'preco_venda' => 39.90, 'custo_medio' => 0, 'estoque' => 0]);
        $suporte = Produto::create(['nome' => 'Suporte de Mesa', 'preco_venda' => 79.90, 'custo_medio' => 0, 'estoque' => 0]);
        $capa = Produto::create(['nome' => 'Capa Protetora', 'preco_venda' => 29.90, 'custo_medio' => 0, 'estoque' => 0]);

        // Primeira compra — entrada de estoque inicial
        DB::transaction(function () use ($fone, $cabo, $suporte, $capa): void {
            $compra = Compra::create(['fornecedor' => 'Tech Distribuidora Ltda', 'total' => 5_325.00]);

            foreach ([
                [$fone, 20, 130.00],
                [$cabo, 50, 14.00],
                [$suporte, 15, 35.00],
                [$capa, 30, 10.00],
            ] as [$produto, $qtd, $preco]) {
                $compra->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $qtd,
                    'preco_unitario' => $preco,
                    'subtotal' => round($qtd * $preco, 2),
                ]);
                $produto->update(['estoque' => $qtd, 'custo_medio' => $preco]);
            }
        });

        // Segunda compra — lote adicional do fone a preco diferente (testa custo medio)
        DB::transaction(function () use ($fone): void {
            $compra = Compra::create(['fornecedor' => 'Imports Express', 'total' => 1_500.00]);
            $compra->itens()->create([
                'produto_id' => $fone->id,
                'quantidade' => 10,
                'preco_unitario' => 150.00,
                'subtotal' => 1_500.00,
            ]);
            // custo medio ponderado: (20*130 + 10*150) / 30 = 136.67
            $fone->update(['estoque' => 30, 'custo_medio' => round((20 * 130 + 10 * 150) / 30, 4)]);
        });

        // Venda
        DB::transaction(function () use ($fone, $cabo): void {
            $precoFone = 299.90;
            $preCabo = 39.90;
            $custoFone = (float) $fone->fresh()->custo_medio;
            $custoCabo = (float) $cabo->custo_medio;

            $venda = Venda::create([
                'cliente' => 'Maria Oliveira',
                'total' => round(3 * $precoFone + 2 * $preCabo, 2),
                'lucro' => round(3 * ($precoFone - $custoFone) + 2 * ($preCabo - $custoCabo), 2),
            ]);

            $venda->itens()->create([
                'produto_id' => $fone->id,
                'quantidade' => 3,
                'preco_unitario' => $precoFone,
                'custo_unitario' => $custoFone,
                'subtotal' => round(3 * $precoFone, 2),
                'lucro' => round(3 * ($precoFone - $custoFone), 2),
            ]);

            $venda->itens()->create([
                'produto_id' => $cabo->id,
                'quantidade' => 2,
                'preco_unitario' => $preCabo,
                'custo_unitario' => $custoCabo,
                'subtotal' => round(2 * $preCabo, 2),
                'lucro' => round(2 * ($preCabo - $custoCabo), 2),
            ]);

            $fone->decrement('estoque', 3);
            $cabo->decrement('estoque', 2);
        });
    }
}
