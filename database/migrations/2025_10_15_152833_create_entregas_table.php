<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            if (Schema::hasColumn('entregas', 'motorista_id')) {
                $table->dropForeign(['motorista_id']);
                $table->dropColumn('motorista_id');
            }

            if (!Schema::hasColumn('entregas', 'funcionario_id')) {
                $table->foreignId('funcionario_id')->after('frota_id')->constrained('funcionarios')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropForeign(['funcionario_id']);
            $table->dropColumn('funcionario_id');

            $table->foreignId('motorista_id')->after('frota_id')->constrained('motoristas')->onDelete('cascade');
        });
    }
};
