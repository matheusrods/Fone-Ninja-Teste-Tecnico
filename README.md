# Fone Ninja — ERP de Estoque

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Vue](https://img.shields.io/badge/Vue-3-42b883?logo=vue.js&logoColor=white)
![Tests](https://img.shields.io/badge/tests-44%20passed-22c55e)

Sistema ERP para controle de estoque com cadastro de produtos, registro de compras (custo médio ponderado) e vendas (cálculo de lucro por item).

---

## Stack

| Camada | Tecnologia |
|---|---|
| API | Laravel 12 + PHP 8.3 |
| Banco | MySQL 8 |
| Frontend | Vue 3 + Vite (Composition API) |
| Ambiente | Docker Compose |
| Testes | PHPUnit 11 (SQLite in-memory) + Larastan |

---

## Como rodar

```bash
docker compose up --build
```

Migrations, seed e servidor sobem automaticamente. Aguarde:

```
fone_ninja_backend | INFO  Server running on [http://0.0.0.0:8000].
fone_ninja_frontend | VITE ready in ...ms
```

| Serviço | URL |
|---|---|
| Frontend | http://localhost:5173 |
| API | http://localhost:8000/api |
| MySQL | localhost:3306 |

---

## Login

O sistema possui autenticação via token (Laravel Sanctum). Ao abrir o frontend, uma tela de login será exibida.

**Usuarios criados automaticamente pelo seed:**

| Perfil | E-mail | Senha | Permissões |
|---|---|---|---|
| Administrador | `admin@foneninja.com` | `password` | Tudo |
| Vendedor | `vendedor@foneninja.com` | `password` | Ver produtos + Registrar/ver vendas |
| Comprador | `comprador@foneninja.com` | `password` | Ver produtos + Registrar/ver compras |

> Para explorar todas as funcionalidades, use o **Administrador**.

---

## Endpoints da API

### Autenticação

| Método | Rota | Descrição |
|---|---|---|
| `POST` | `/api/login` | Retorna Bearer token |
| `POST` | `/api/logout` | Invalida o token (requer auth) |
| `GET` | `/api/me` | Dados do usuário autenticado (requer auth) |

> Todos os endpoints abaixo exigem o header `Authorization: Bearer {token}`.

### Produtos

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/produtos` | Lista produtos ordenados por nome |
| `POST` | `/api/produtos` | Cadastra produto |
| `PATCH` | `/api/produtos/{id}` | Atualiza nome e/ou preço de venda |
| `DELETE` | `/api/produtos/{id}` | Arquiva produto (soft delete) |

### Compras

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/compras` | Lista histórico paginado de compras |
| `POST` | `/api/compras` | Registra compra e atualiza estoque |

### Vendas

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/vendas` | Lista histórico paginado de vendas |
| `POST` | `/api/vendas` | Registra venda e calcula lucro |
| `POST` | `/api/vendas/{id}/cancelar` | Cancela venda e reverte estoque |

---

## Exemplos de payload

**Login:**
```json
POST /api/login
{ "email": "admin@foneninja.com", "password": "password" }
```

**Cadastrar produto:**
```json
POST /api/produtos
{ "nome": "Fone Bluetooth Pro", "preco_venda": 299.90 }
```

**Registrar compra:**
```json
POST /api/compras
{
  "fornecedor": "Tech Distribuidora",
  "produtos": [
    { "id": 1, "quantidade": 50, "preco_unitario": 20.00 },
    { "id": 2, "quantidade": 30, "preco_unitario": 10.00 }
  ]
}
```

**Registrar venda:**
```json
POST /api/vendas
{
  "cliente": "Fulano da Silva",
  "produtos": [
    { "id": 1, "quantidade": 2, "preco_unitario": 50.00 },
    { "id": 2, "quantidade": 1, "preco_unitario": 100.00 }
  ]
}
```

---

## Testes

```bash
docker compose exec backend php artisan test
```

```
PASS  Tests\Unit\Services\CompraServiceTest    (4 testes)
PASS  Tests\Unit\Services\VendaServiceTest     (4 testes)
PASS  Tests\Feature\AuthTest                   (9 testes)
PASS  Tests\Feature\CompraTest                 (6 testes)
PASS  Tests\Feature\ProdutoTest                (12 testes)
PASS  Tests\Feature\VendaTest                  (9 testes)

Tests: 44 passed (125 assertions)
```

**Análise estática (PHPStan nível 5):**
```bash
docker compose exec backend vendor/bin/phpstan analyse --memory-limit=512M
# [OK] No errors
```

---

## Arquitetura do backend

```
backend/app/
├── DTOs/                        # Objetos tipados de transferência entre camadas
├── Enums/                       # Role, VendaStatus
├── Events/                      # VendaRegistrada, CompraRegistrada, VendaCancelada
├── Exceptions/                  # EstoqueInsuficienteException, VendaJaCanceladaException
├── Http/
│   ├── Controllers/Api/         # Controllers finos — apenas HTTP
│   ├── Middleware/              # SecurityHeadersMiddleware
│   ├── Requests/                # Form Requests com validação
│   └── Resources/               # API Resources (formatação de resposta)
├── Listeners/                   # LogMovimentacaoEstoque
├── Models/                      # Produto (SoftDeletes), Compra, Venda, User
├── Policies/                    # ProdutoPolicy, CompraPolicy, VendaPolicy
├── Repositories/
│   ├── Contracts/               # Interfaces desacopladas da implementação
│   └── Eloquent*Repository.php  # Implementações Eloquent
└── Services/
    ├── Contracts/               # Interfaces de serviço
    └── *Service.php             # Regras de negócio
```

---

## Regras de negócio implementadas

- Nome do produto: mínimo 3 caracteres e único no sistema
- Preço de venda: deve ser positivo
- Compra: atualiza estoque e recalcula **custo médio ponderado** (4 casas decimais)
- Venda: valida estoque, baixa estoque e calcula lucro por item usando o custo médio do momento
- Produto não pode aparecer duplicado na mesma compra/venda
- Cancelamento de venda reverte o estoque de cada item (inclusive produtos arquivados)
- Produto arquivado (soft delete) some da listagem mas mantém o histórico de compras/vendas
- Todas as operações de estoque rodam em **transação com `lockForUpdate`** (seguro para concorrência)
- Tokens de autenticação expiram em **24 horas**
- Rate limiting: 60 req/min por usuário autenticado; 10 req/min por IP no login

---

## Segurança

- Autenticação via **Laravel Sanctum** (Bearer token)
- Autorização por perfil via **Policies** (admin, vendedor, comprador)
- Headers de segurança em todas as respostas (`X-Content-Type-Options`, `X-Frame-Options`, etc.)
- Rate limiting nas rotas de login e API
- CORS configurado para aceitar apenas o frontend (`localhost:5173`)

---

## Estrutura Docker

```
projeto-teste-sioux/
├── docker-compose.yml
├── backend/
│   └── Dockerfile
└── frontend/
    └── Dockerfile
```

O backend roda com `php artisan serve` (porta 8000) e o frontend com `vite --host` (porta 5173). O MySQL aguarda o healthcheck antes de aceitar conexões do backend.
