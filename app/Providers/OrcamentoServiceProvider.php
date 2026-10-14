<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Orcamento;

class OrcamentoServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Atualiza todos os orçamentos expirados apenas uma vez por request
        // REC01_BOOT_FIX_V1: expiracao transferida para o Scheduler.
    }
}
