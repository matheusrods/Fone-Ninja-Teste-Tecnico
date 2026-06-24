<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table): void {
            $table->id();
            $table->string('cliente');
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('lucro', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('venda_itens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->unsignedInteger('quantidade');
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('custo_unitario', 10, 4);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('lucro', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_itens');
        Schema::dropIfExists('vendas');
    }
};
