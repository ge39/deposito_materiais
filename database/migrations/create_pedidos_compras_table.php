<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos_compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fornecedor_id');
            $table->date('data_pedido');
            $table->enum('status', ['pendente', 'recebido', 'cancelado'])->default('pendente');
            $table->decimal('total', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('fornecedor_id')
                  ->references('id')
                  ->on('fornecedores')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos_compras');
    }
};
