<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BI\BiCaixasGerencialController;

Route::middleware(['auth'])
    ->prefix('bi/caixas-gerencial')
    ->name('bi.caixas-gerencial.')
    ->group(function () {
        Route::get('/', [BiCaixasGerencialController::class, 'index'])
            ->name('index');

        Route::get('/dados', [BiCaixasGerencialController::class, 'dados'])
            ->name('dados');

        Route::get('/detalhes/{caixa}', [BiCaixasGerencialController::class, 'detalhes'])
            ->whereNumber('caixa')
            ->name('detalhes');
    });