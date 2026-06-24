<?php

namespace App\Providers;

use App\Events\CompraRegistrada;
use App\Events\VendaCancelada;
use App\Events\VendaRegistrada;
use App\Listeners\LogMovimentacaoEstoque;
use App\Models\Compra;
use App\Models\Produto;
use App\Models\Venda;
use App\Policies\CompraPolicy;
use App\Policies\ProdutoPolicy;
use App\Policies\VendaPolicy;
use App\Repositories\Contracts\CompraRepositoryInterface;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Repositories\Contracts\VendaRepositoryInterface;
use App\Repositories\EloquentCompraRepository;
use App\Repositories\EloquentProdutoRepository;
use App\Repositories\EloquentVendaRepository;
use App\Services\CompraService;
use App\Services\Contracts\CompraServiceInterface;
use App\Services\Contracts\ProdutoServiceInterface;
use App\Services\Contracts\VendaServiceInterface;
use App\Services\ProdutoService;
use App\Services\VendaService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProdutoRepositoryInterface::class, EloquentProdutoRepository::class);
        $this->app->bind(CompraRepositoryInterface::class, EloquentCompraRepository::class);
        $this->app->bind(VendaRepositoryInterface::class, EloquentVendaRepository::class);

        $this->app->bind(ProdutoServiceInterface::class, ProdutoService::class);
        $this->app->bind(CompraServiceInterface::class, CompraService::class);
        $this->app->bind(VendaServiceInterface::class, VendaService::class);
    }

    public function boot(): void
    {
        $this->configurePolicies();
        $this->configureRateLimiting();
        $this->configureEvents();
    }

    private function configurePolicies(): void
    {
        Gate::policy(Produto::class, ProdutoPolicy::class);
        Gate::policy(Compra::class, CompraPolicy::class);
        Gate::policy(Venda::class, VendaPolicy::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request): array {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(5)->by($request->input('email')),
            ];
        });
    }

    private function configureEvents(): void
    {
        Event::listen(CompraRegistrada::class, [LogMovimentacaoEstoque::class, 'handleCompra']);
        Event::listen(VendaRegistrada::class, [LogMovimentacaoEstoque::class, 'handleVenda']);
        Event::listen(VendaCancelada::class, [LogMovimentacaoEstoque::class, 'handleVendaCancelada']);
    }
}
