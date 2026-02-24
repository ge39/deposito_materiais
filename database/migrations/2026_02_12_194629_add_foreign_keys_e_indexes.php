<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Tabela: caixas
         * Preservamos o existente, adicionamos apenas FKs se necessário
         */
        Schema::table('caixas', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_caixas_user')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        /**
         * Tabela: vendas
         */
        Schema::table('vendas', function (Blueprint $table) {
            $table->foreign('caixa_id', 'fk_vendas_caixa')
                  ->references('id')->on('caixas')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('cliente_id', 'fk_vendas_cliente')
                  ->references('id')->on('clientes')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('funcionario_id', 'fk_vendas_funcionario')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index(['caixa_id'], 'idx_vendas_caixa');
            $table->index(['status'], 'idx_vendas_status');
        });

        /**
         * Tabela: pagamentos_venda
         */
        Schema::table('pagamentos_venda', function (Blueprint $table) {
            $table->foreign('venda_id', 'fk_pagamentos_venda_venda')
                  ->references('id')->on('vendas')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('caixa_id', 'fk_pagamentos_venda_caixa')
                  ->references('id')->on('caixas')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('user_id', 'fk_pagamentos_venda_user')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index(['venda_id'], 'idx_pagamentos_venda_id');
            $table->index(['forma_pagamento'], 'idx_pagamentos_forma');
            $table->index(['venda_id','forma_pagamento'], 'idx_pagamentos_venda_forma');
            $table->index(['bandeira'], 'idx_pagamentos_venda_bandeira');
        });

        /**
         * Tabela: auditorias_caixa
         */
        Schema::table('auditorias_caixa', function (Blueprint $table) {
            $table->foreign('caixa_id', 'fk_auditorias_caixa_caixa')
                  ->references('id')->on('caixas')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->foreign('user_id', 'fk_auditorias_caixa_user')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            $table->index(['caixa_id'], 'idx_auditorias_caixa_caixa');
            $table->index(['status'], 'idx_auditorias_caixa_status');
        });

        /**
         * Tabela: auditoria_detalhes
         */
        Schema::table('auditoria_detalhes', function (Blueprint $table) {
            $table->foreign('auditoria_id', 'fk_auditoria_detalhes_auditoria')
                  ->references('id')->on('auditorias_caixa')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->index(['auditoria_id'], 'idx_auditoria_detalhes_auditoria');
            $table->index(['forma_pagamento'], 'idx_auditoria_detalhes_forma');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_detalhes', function (Blueprint $table) {
            $table->dropForeign('fk_auditoria_detalhes_auditoria');
            $table->dropIndex('idx_auditoria_detalhes_auditoria');
            $table->dropIndex('idx_auditoria_detalhes_forma');
        });

        Schema::table('auditorias_caixa', function (Blueprint $table) {
            $table->dropForeign('fk_auditorias_caixa_caixa');
            $table->dropForeign('fk_auditorias_caixa_user');
            $table->dropIndex('idx_auditorias_caixa_caixa');
            $table->dropIndex('idx_auditorias_caixa_status');
        });

        Schema::table('pagamentos_venda', function (Blueprint $table) {
            $table->dropForeign('fk_pagamentos_venda_venda');
            $table->dropForeign('fk_pagamentos_venda_caixa');
            $table->dropForeign('fk_pagamentos_venda_user');
            $table->dropIndex('idx_pagamentos_venda_id');
            $table->dropIndex('idx_pagamentos_forma');
            $table->dropIndex('idx_pagamentos_venda_forma');
            $table->dropIndex('idx_pagamentos_venda_bandeira');
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropForeign('fk_vendas_caixa');
            $table->dropForeign('fk_vendas_cliente');
            $table->dropForeign('fk_vendas_funcionario');
            $table->dropIndex('idx_vendas_caixa');
            $table->dropIndex('idx_vendas_status');
        });

        Schema::table('caixas', function (Blueprint $table) {
            $table->dropForeign('fk_caixas_user');
        });
    }
};
