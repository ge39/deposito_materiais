<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->foreignId('funcionario_id')
                  ->after('frota_id')
                  ->constrained('funcionarios')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropForeign(['funcionario_id']);
            $table->dropColumn('funcionario_id');
        });
    }
};
