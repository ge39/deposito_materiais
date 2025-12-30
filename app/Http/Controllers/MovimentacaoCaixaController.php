<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;

class MovimentacaoCaixaController extends Controller
{
    /**
     * Registra movimentação de abertura de caixa
     */
    public function registrarAbertura(Caixa $caixa, float $valor): void
    {
        MovimentacaoCaixa::create([
            'caixa_id'          => $caixa->id,
            'user_id'           => auth()->id(),
            'tipo'              => 'abertura',
            'valor'             => $valor,
            'observacao'        => 'Abertura de caixa (fundo de troco)',
            'data_movimentacao' => now(),
        ]);
    }

    // Próximos métodos:
    // registrarSangria()
    // registrarSuprimento()
    // registrarFechamento()
}
