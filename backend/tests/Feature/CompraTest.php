<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraTest extends TestCase
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
            'custo_medio' => 0,
            'estoque' => 0,
        ], $attrs));
    }

    public function test_registra_compra_e_atualiza_estoque(): void
    {
        $produto = $this->produto();

        $this->postJson("{$this->api}/compras", [
            'fornecedor' => 'Distribuidora X',
            'produtos' => [['id' => $produto->id, 'quantidade' => 10, 'preco_unitario' => 50.00]],
        ])
            ->assertCreated()
            ->assertJsonPath('data.fornecedor', 'Distribuidora X')
            ->assertJsonPath('data.total', 500);

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 10, 'custo_medio' => 50.0000]);
    }

    public function test_custo_medio_ponderado_em_segunda_compra(): void
    {
        $produto = $this->produto(['estoque' => 10, 'custo_medio' => 50.0000]);

        $this->postJson("{$this->api}/compras", [
            'fornecedor' => 'Fornecedor Y',
            'produtos' => [['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 80.00]],
        ])->assertCreated();

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 15, 'custo_medio' => 60.0000]);
    }

    public function test_rejeita_produto_duplicado_na_mesma_compra(): void
    {
        $produto = $this->produto();

        $this->postJson("{$this->api}/compras", [
            'fornecedor' => 'Fornecedor',
            'produtos' => [
                ['id' => $produto->id, 'quantidade' => 5, 'preco_unitario' => 50.00],
                ['id' => $produto->id, 'quantidade' => 3, 'preco_unitario' => 50.00],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['produtos']);

        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'estoque' => 0]);
    }

    public function test_compra_com_multiplos_produtos_distintos(): void
    {
        $a = $this->produto(['nome' => 'Produto A']);
        $b = $this->produto(['nome' => 'Produto B']);

        $this->postJson("{$this->api}/compras", [
            'fornecedor' => 'Fornecedor Z',
            'produtos' => [
                ['id' => $a->id, 'quantidade' => 3, 'preco_unitario' => 10.00],
                ['id' => $b->id, 'quantidade' => 2, 'preco_unitario' => 20.00],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.total', 70);

        $this->assertDatabaseHas('produtos', ['id' => $a->id, 'estoque' => 3]);
        $this->assertDatabaseHas('produtos', ['id' => $b->id, 'estoque' => 2]);
    }

    public function test_listagem_retorna_paginacao(): void
    {
        $this->getJson("{$this->api}/compras")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total'], 'links']);
    }

    public function test_vendedor_nao_pode_registrar_compra(): void
    {
        $this->loginAs('vendedor');
        $produto = $this->produto();

        $this->postJson("{$this->api}/compras", [
            'fornecedor' => 'Fornecedor',
            'produtos' => [['id' => $produto->id, 'quantidade' => 1, 'preco_unitario' => 10]],
        ])->assertForbidden();
    }
}
