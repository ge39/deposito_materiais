<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class BloqueioCreditoService
{
    public function reavaliarCliente(Cliente $cliente): void
    {
        $credito = app(CreditoService::class);

        $saldo = $credito->saldoDevedor($cliente);

        $bloquearPorLimite = $cliente->limite_credito > 0
            && $saldo >= $cliente->limite_credito;

        $bloquearPorAtraso = $credito->possuiPagamentoEmAtraso($cliente);

        $deveBloquear = $bloquearPorLimite || $bloquearPorAtraso;

        if ($deveBloquear && !$cliente->bloqueado_credito) {
            $this->bloquear($cliente);
        }

        if (!$deveBloquear && $cliente->bloqueado_credito) {
            $this->desbloquear($cliente);
        }
    }

    private function bloquear(Cliente $cliente): void
    {
        $cliente->bloqueado_credito = 1;
        $cliente->data_bloqueio_credito = now();
        $cliente->save();

        $this->ajustarScore($cliente, -10);
        $this->registrarHistorico($cliente, 'bloqueio');
    }

    private function desbloquear(Cliente $cliente): void
    {
        $cliente->bloqueado_credito = 0;
        $cliente->data_bloqueio_credito = null;
        $cliente->save();

        $this->ajustarScore($cliente, +5);
        $this->registrarHistorico($cliente, 'desbloqueio');
    }

    private function ajustarScore(Cliente $cliente, int $variacao): void
    {
        $anterior = $cliente->score_credito;

        $cliente->score_credito += $variacao;
        $cliente->score_credito = max(0, min(100, $cliente->score_credito));
        $cliente->save();

        DB::table('clientes_historico_credito')->insert([
            'cliente_id' => $cliente->id,
            'tipo_evento' => 'ajuste_score',
            'descricao' => 'Ajuste automático',
            'score_anterior' => $anterior,
            'score_novo' => $cliente->score_credito,
            'created_at' => now()
        ]);
    }

    private function registrarHistorico(Cliente $cliente, string $tipo): void
    {
        DB::table('clientes_historico_credito')->insert([
            'cliente_id' => $cliente->id,
            'tipo_evento' => $tipo,
            'descricao' => 'Evento automático de crédito',
            'created_at' => now()
        ]);
    }
}