<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoTest extends TestCase
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

    public function test_lista_produtos_ordenados_por_nome(): void
    {
        Produto::create(['nome' => 'Fone Bluetooth', 'preco_venda' => 99.90, 'custo_medio' => 0, 'estoque' => 0]);
        Produto::create(['nome' => 'Amplificador', 'preco_venda' => 299.90, 'custo_medio' => 0, 'estoque' => 0]);

        $this->getJson("{$this->api}/produtos")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nome', 'Amplificador')
            ->assertJsonPath('data.1.nome', 'Fone Bluetooth');
    }

    public function test_cadastra_produto_com_estoque_zero(): void
    {
        $this->postJson("{$this->api}/produtos", ['nome' => 'Fone Gamer', 'preco_venda' => 199.90])
            ->assertCreated()
            ->assertJsonPath('data.nome', 'Fone Gamer')
            ->assertJsonPath('data.estoque', 0)
            ->assertJsonPath('data.custo_medio', 0);
    }

    public function test_nome_deve_ser_unico(): void
    {
        $this->produto(['nome' => 'Fone X']);

        $this->postJson("{$this->api}/produtos", ['nome' => 'Fone X', 'preco_venda' => 50])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_nome_minimo_3_caracteres(): void
    {
        $this->postJson("{$this->api}/produtos", ['nome' => 'AB', 'preco_venda' => 10])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_preco_venda_deve_ser_positivo(): void
    {
        $this->postJson("{$this->api}/produtos", ['nome' => 'Produto', 'preco_venda' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['preco_venda']);
    }

    public function test_campos_obrigatorios(): void
    {
        $this->postJson("{$this->api}/produtos", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nome', 'preco_venda']);
    }

    public function test_atualiza_preco_de_venda(): void
    {
        $produto = $this->produto(['nome' => 'Fone Z', 'preco_venda' => 100]);

        $this->patchJson("{$this->api}/produtos/{$produto->id}", ['preco_venda' => 149.90])
            ->assertOk()
            ->assertJsonPath('data.preco_venda', 149.90);
    }

    public function test_atualiza_nome_ignorando_o_proprio_produto(): void
    {
        $produto = $this->produto(['nome' => 'Fone Antigo']);

        $this->patchJson("{$this->api}/produtos/{$produto->id}", ['nome' => 'Fone Novo'])
            ->assertOk()
            ->assertJsonPath('data.nome', 'Fone Novo');
    }

    public function test_nao_permite_nome_duplicado_na_atualizacao(): void
    {
        $this->produto(['nome' => 'Produto A']);
        $produtoB = $this->produto(['nome' => 'Produto B']);

        $this->patchJson("{$this->api}/produtos/{$produtoB->id}", ['nome' => 'Produto A'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_exclui_produto_com_soft_delete(): void
    {
        $produto = $this->produto(['nome' => 'Para Excluir']);

        $this->deleteJson("{$this->api}/produtos/{$produto->id}")->assertNoContent();

        $this->assertSoftDeleted('produtos', ['id' => $produto->id]);
        $this->assertDatabaseMissing('produtos', ['id' => $produto->id, 'deleted_at' => null]);
    }

    public function test_produto_excluido_nao_aparece_na_listagem(): void
    {
        $produto = $this->produto(['nome' => 'Excluido']);
        $produto->delete();

        $this->getJson("{$this->api}/produtos")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_vendedor_nao_pode_cadastrar_produto(): void
    {
        $this->loginAs('vendedor');

        $this->postJson("{$this->api}/produtos", ['nome' => 'Produto', 'preco_venda' => 100])
            ->assertForbidden();
    }
}
