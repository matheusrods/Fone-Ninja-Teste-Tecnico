<?php

namespace Tests\Unit\Services;

use App\Services\VendaService;
use PHPUnit\Framework\TestCase;

class VendaServiceTest extends TestCase
{
    public function test_calcula_lucro_positivo(): void
    {
        $lucro = VendaService::calcularLucroItem(
            preco: 100.0,
            custo: 60.0,
            quantidade: 3,
        );

        $this->assertEquals(120.0, $lucro); // 3 * (100 - 60)
    }

    public function test_calcula_lucro_negativo_quando_vende_abaixo_do_custo(): void
    {
        $lucro = VendaService::calcularLucroItem(
            preco: 40.0,
            custo: 60.0,
            quantidade: 2,
        );

        $this->assertEquals(-40.0, $lucro); // 2 * (40 - 60)
    }

    public function test_calcula_lucro_zero_quando_preco_igual_ao_custo(): void
    {
        $lucro = VendaService::calcularLucroItem(
            preco: 50.0,
            custo: 50.0,
            quantidade: 5,
        );

        $this->assertEquals(0.0, $lucro);
    }

    public function test_calcula_lucro_com_quantidade_unitaria(): void
    {
        $lucro = VendaService::calcularLucroItem(
            preco: 299.90,
            custo: 136.6667,
            quantidade: 1,
        );

        $this->assertEqualsWithDelta(163.2333, $lucro, 0.0001);
    }
}
