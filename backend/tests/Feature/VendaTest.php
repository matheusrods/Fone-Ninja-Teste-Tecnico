<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    private function produto(array $attrs = []): Produto
    {
        return Produto::create(array_merge([
            'nome' => 'Produto Teste',
            'preco_venda' => 100,
            'custo_medio' => 50,
            'estoque' => 20,
        ], $attrs));
    }

    private function venda(Produto $produto, int $quantidade = 2): Venda
    {
        $venda = Venda::create([
            'cliente' => 'Cliente Test',
            'total' => $quantidade * 100,
            'lucro' => $quantidade * 50,
        ]);

        $venda->itens()->create([
            'produto_id' => $produto->id,
            'quantidade' => $quantidade,
            'preco_unitario' => 100,
            'custo_unitario' => 50,
            'subtotal' => $quantidade * 100,
            'lucro' => $quantidade * 50,
        ]);

        $produto->decrement('estoque', $quantidade);

        return $venda;
    }

    public function test_registra_venda_e_baixa_estoque(): void
    {
        $produto = $this->produto();

        $this->postJson("{$this->api}/vendas", [
            'cliente' => 'Cliente A',
            'produtos' => [['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 100.00]],
        ])
            ->assertCreated()
            ->assertJsonPath('data.total', 500)
            ->assertJsonPath('data.lucro', 250)
            ->assertJsonPath('data.status', 'ativa');

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 15]);
    }

    public function test_calcula_lucro_usando_custo_medio(): void
    {
        $produto = $this->produto(['custo_medio' => 30, 'preco_venda' => 100]);

        $this->postJson("{$this->api}/vendas", [
            'cliente' => 'Cliente B',
            'produtos' => [['id' => $produto->id, 'quantidade' => 2, 'preco_unitario' => 80.00]],
        ])
            ->assertCreated()
            ->assertJsonPath('data.lucro', 100);
    }

    public function test_rejeita_estoque_insuficiente(): void
    {
        $produto = $this->produto(['estoque' => 3]);

        $this->postJson("{$this->api}/vendas", [
            'cliente' => 'Cliente C',
            'produtos' => [['id' => $produto->id, 'quantidade' => 10, 'preco_unitario' => 100.00]],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['estoque']);

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 3]);
    }

    public function test_rejeita_produto_duplicado_na_mesma_venda(): void
    {
        $produto = $this->produto(['estoque' => 5]);

        $this->postJson("{$this->api}/vendas", [
            'cliente' => 'Cliente D',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 3, 'preco_unitario' => 100.00],
                ['id' => $produto->id, 'quantidade' => 2, 'preco_unitario' => 100.00],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['produtos']);

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 5]);
    }

    public function test_produto_duplicado_nao_gera_estoque_negativo(): void
    {
        $produto = $this->produto(['estoque' => 6]);

        $this->postJson("{$this->api}/vendas", [
            'cliente' => 'Cliente E',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 100.00],
                ['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 100.00],
            ],
        ])->assertUnprocessable();

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 6]);
    }

    public function test_cancela_venda_reverte_estoque(): void
    {
        $produto = $this->produto(['estoque' => 20]);
        $venda = $this->venda($produto, 5);

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 15]);

        $this->postJson("{$this->api}/vendas/{$venda->id}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelada');

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 20]);
        $this->assertDatabaseHas('vendas', ['id' => $venda->id, 'status' => 'cancelada']);
    }

    public function test_nao_cancela_venda_ja_cancelada(): void
    {
        $produto = $this->produto();
        $venda = $this->venda($produto);
        $venda->update(['status' => 'cancelada']);

        $this->postJson("{$this->api}/vendas/{$venda->id}/cancelar")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_listagem_retorna_paginacao(): void
    {
        $this->getJson("{$this->api}/vendas")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total'], 'links']);
    }

    public function test_comprador_nao_pode_registrar_venda(): void
    {
        $this->loginAs('comprador');
        $produto = $this->produto();

        $this->postJson("{$this->api}/vendas", [
            'cliente' => 'Cliente',
            'produtos' => [['id' => $produto->id, 'quantidade' => 1, 'preco_unitario' => 100]],
        ])->assertForbidden();
    }
}
