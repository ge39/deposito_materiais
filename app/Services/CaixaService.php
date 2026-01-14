<?php
// app/Services/CaixaService.php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\Caixa;

class CaixaService
{
    public static function caixaAbertoParaUsuario($userId)
    {
        return Caixa::where('user_id', $userId)
            ->where('status', 'aberto')
            ->first();
    }

    public static function registrarMovimentacaoCaixa(array $dados)
    {
        if (!isset($dados['caixa_id'], $dados['user_id'], $dados['tipo'], $dados['valor'])) {
            throw new \InvalidArgumentException('Parâmetros obrigatórios não fornecidos para movimentação de caixa.');
        }

        $tiposPermitidos = ['abertura','venda','entrada_manual','saida_manual','cancelamento_venda','fechamento'];
        if (!in_array($dados['tipo'], $tiposPermitidos)) {
            throw new \InvalidArgumentException("Tipo de movimentação inválido: {$dados['tipo']}");
        }

        DB::table('movimentacoes_caixa')->insert([
            'caixa_id'        => $dados['caixa_id'],
            'user_id'         => $dados['user_id'],
            'tipo'            => $dados['tipo'],
            'valor'           => (float) $dados['valor'],
            'forma_pagamento' => $dados['forma_pagamento'] ?? null,
            'bandeira'        => $dados['bandeira'] ?? null,
            'origem_id'       => $dados['origem_id'] ?? null,
            'observacao'      => $dados['observacao'] ?? null,
            'data_movimentacao' => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        \Log::info("Movimentação de caixa registrada", $dados);
    }
}
