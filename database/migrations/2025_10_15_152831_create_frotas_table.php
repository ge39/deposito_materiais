<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frotas', function (Blueprint $table) {
            $table->id();
            $table->string('veiculo');
            $table->string('placa')->nullable();
            $table->integer('capacidade')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frotas');
    }
};
