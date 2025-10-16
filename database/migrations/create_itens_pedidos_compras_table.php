<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('itens_pedido_compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('produto_id');
            $table->integer('quantidade')->unsigned();
            $table->decimal('preco_unitario', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();

            // Foreign keys
            $table->foreign('pedido_id')
                  ->references('id')
                  ->on('pedidos_compras')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('produto_id')
                  ->references('id')
                  ->on('produtos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('itens_pedido_compras');
    }
};
