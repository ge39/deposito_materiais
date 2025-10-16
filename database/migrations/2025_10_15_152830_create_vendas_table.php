<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'funcionario_id')) {
                $table->foreignId('funcionario_id')->after('cliente_id')->constrained('funcionarios')->onDelete('cascade');
            }

            if (!Schema::hasColumn('vendas', 'data_venda')) {
                $table->date('data_venda')->after('funcionario_id');
            }

            if (!Schema::hasColumn('vendas', 'total')) {
                $table->decimal('total', 10, 2)->default(0.00)->after('data_venda');
            }

            if (!Schema::hasColumn('vendas', 'status')) {
                $table->enum('status', ['pendente','concluida','cancelada'])->default('pendente')->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (Schema::hasColumn('vendas', 'funcionario_id')) {
                $table->dropForeign(['funcionario_id']);
                $table->dropColumn('funcionario_id');
            }

            if (Schema::hasColumn('vendas', 'data_venda')) {
                $table->dropColumn('data_venda');
            }

            if (Schema::hasColumn('vendas', 'total')) {
                $table->dropColumn('total');
            }

            if (Schema::hasColumn('vendas', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
