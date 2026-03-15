<?php

namespace App\Services;

use App\Models\Sangria;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class SangriaService
{
    /**
     * Registrar uma sangria
     *
     * @param Caixa $caixa
     * @param float $valor
     * @param string $motivo
     * @return Sangria
     * @throws Exception
     */
    public function registrarSangria(Caixa $caixa, float $valor, string $motivo = 'manual'): Sangria
    {
        // 1️⃣ Calcular saldo em dinheiro disponível
        $entradas = $caixa->saldoDinheiroEntradas();
        $saidas = $caixa->saldoDinheiroSaidas();
        $saldoDisponivel = $entradas - $saidas;

        if ($valor > $saldoDisponivel) {
            throw new Exception("Valor da sangria ({$valor}) não pode ser maior que o saldo disponível ({$saldoDisponivel})");
        }

        return DB::transaction(function () use ($caixa, $valor, $motivo, $saldoDisponivel) {

            $userId = Auth::id();

            $codigoOperacao = 'SNG-' . $caixa->numero_pdv . '-' . now()->format('YmdHis');

            // 2️⃣ Criar registro na tabela sangrias
            $sangria = Sangria::create([
                'empresa_id' => $caixa->empresa_id,
                'caixa_id' => $caixa->id,
                'user_id' => $userId,
                'codigo_operacao' => $codigoOperacao,
                'numero_pdv' => $caixa->numero_pdv,
                'valor' => $valor,
                'saldo_antes' => $saldoDisponivel,
                'saldo_depois' => $saldoDisponivel - $valor,
                'motivo' => $motivo
            ]);

            // 3️⃣ Criar movimentação automática no caixa
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'auditoria_id' => null,
                'tipo' => 'saida_manual',
                'forma_pagamento' => 'sangria',
                'valor' => $valor,
                'observacao' => 'Sangria registrada - código '.$codigoOperacao,
                'user_id' => $userId,
                'data_movimentacao' => now(),
            ]);

            return $sangria;
        });
    }
}