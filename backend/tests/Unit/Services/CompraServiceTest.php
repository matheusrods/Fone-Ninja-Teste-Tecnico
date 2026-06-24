<?php

namespace Tests\Unit\Services;

use App\Services\CompraService;
use PHPUnit\Framework\TestCase;

class CompraServiceTest extends TestCase
{
    public function test_custo_medio_ponderado_primeira_compra(): void
    {
        // estoque zerado + primeira compra a R$50
        $resultado = CompraService::calcularNovoCustoMedio(
            estoqueAtual: 0,
            custoAtual: 0.0,
            qtdComprada: 10,
            precoUnitario: 50.0,
        );

        $this->assertEquals(50.0, $resultado);
    }

    public function test_custo_medio_ponderado_segunda_compra(): void
    {
        // 10 unidades a R$50 + 5 unidades a R$80 = custo medio R$60
        $resultado = CompraService::calcularNovoCustoMedio(
            estoqueAtual: 10,
            custoAtual: 50.0,
            qtdComprada: 5,
            precoUnitario: 80.0,
        );

        $this->assertEquals(60.0, $resultado);
    }

    public function test_custo_medio_ponderado_com_preco_igual(): void
    {
        // compra adicional ao mesmo preco nao altera o custo medio
        $resultado = CompraService::calcularNovoCustoMedio(
            estoqueAtual: 10,
            custoAtual: 50.0,
            qtdComprada: 10,
            precoUnitario: 50.0,
        );

        $this->assertEquals(50.0, $resultado);
    }

    public function test_custo_medio_ponderado_com_preco_menor(): void
    {
        // 10 a R$60 + 10 a R$40 = media R$50
        $resultado = CompraService::calcularNovoCustoMedio(
            estoqueAtual: 10,
            custoAtual: 60.0,
            qtdComprada: 10,
            precoUnitario: 40.0,
        );

        $this->assertEquals(50.0, $resultado);
    }
}
