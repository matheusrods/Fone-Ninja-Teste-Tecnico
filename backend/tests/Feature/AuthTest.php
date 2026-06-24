<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuario(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    public function test_login_com_credenciais_validas(): void
    {
        $this->criarUsuario(['email' => 'admin@test.com', 'password' => 'password', 'role' => Role::Admin]);

        $this->postJson("{$this->api}/login", ['email' => 'admin@test.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_com_credenciais_invalidas(): void
    {
        $this->postJson("{$this->api}/login", ['email' => 'nao@existe.com', 'password' => 'errada'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_valida_campos_obrigatorios(): void
    {
        $this->postJson("{$this->api}/login", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_rota_protegida_sem_token_retorna_401(): void
    {
        $this->getJson("{$this->api}/produtos")->assertUnauthorized();
        $this->getJson("{$this->api}/compras")->assertUnauthorized();
        $this->getJson("{$this->api}/vendas")->assertUnauthorized();
    }

    public function test_logout_invalida_token(): void
    {
        $user = $this->criarUsuario();
        $this->actingAs($user, 'sanctum');

        $this->postJson("{$this->api}/logout")->assertOk();

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_me_retorna_dados_do_usuario_autenticado(): void
    {
        $user = $this->criarUsuario(['name' => 'Joao Silva', 'role' => Role::Vendedor]);
        $this->actingAs($user, 'sanctum');

        $this->getJson("{$this->api}/me")
            ->assertOk()
            ->assertJsonPath('data.name', 'Joao Silva')
            ->assertJsonPath('data.role', Role::Vendedor->value);
    }

    public function test_vendedor_nao_pode_ver_compras(): void
    {
        $this->loginAs('vendedor');

        $this->getJson("{$this->api}/compras")->assertForbidden();
    }

    public function test_comprador_nao_pode_ver_vendas(): void
    {
        $this->loginAs('comprador');

        $this->getJson("{$this->api}/vendas")->assertForbidden();
    }

    public function test_admin_pode_acessar_tudo(): void
    {
        $this->loginAs('admin');

        $this->getJson("{$this->api}/produtos")->assertOk();
        $this->getJson("{$this->api}/compras")->assertOk();
        $this->getJson("{$this->api}/vendas")->assertOk();
    }
}
