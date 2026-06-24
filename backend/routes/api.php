<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\VendaController;
use Illuminate\Support\Facades\Route;

Route::group([], function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/produtos', [ProdutoController::class, 'index']);
        Route::post('/produtos', [ProdutoController::class, 'store']);
        Route::patch('/produtos/{produto}', [ProdutoController::class, 'update']);
        Route::delete('/produtos/{produto}', [ProdutoController::class, 'destroy']);

        Route::get('/compras', [CompraController::class, 'index']);
        Route::post('/compras', [CompraController::class, 'store']);

        Route::get('/vendas', [VendaController::class, 'index']);
        Route::post('/vendas', [VendaController::class, 'store']);
        Route::post('/vendas/{venda}/cancelar', [VendaController::class, 'cancelar']);
    });
});
