<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Caixa;

class CaixaService
{
    /**
     * Retorna o caixa aberto do usuário
     */
    public static function caixaAbertoParaUsuario($userId)
    {
        return Caixa::where('user_id', $userId)
            ->where('status', 'aberto')
            ->first();
    }

    /**
     * Registra movimentações administrativas e ajustes de caixa
     * NÃO registra vendas
     */
    public static function registrarMovimentacaoCaixa(array $dados)
    {
        if (!isset($dados['caixa_id'], $dados['user_id'], $dados['tipo'], $dados['valor'])) {
            throw new \InvalidArgumentException(
                'Parâmetros obrigatórios não fornecidos para movimentação de caixa.'
            );
        }

        /**
         * Tipos permitidos (modelo definitivo)
         */
        $tiposPermitidos = [
            'abertura',
            'venda',
            'entrada_manual',
            'saida_manual',
            'cancelamento_venda',
            'fechamento'
        ];

        if (!in_array($dados['tipo'], $tiposPermitidos, true)) {
            throw new \InvalidArgumentException(
                "Tipo de movimentação inválido: {$dados['tipo']}"
            );
        }

        // 🔴 NORMALIZAÇÃO OBRIGATÓRIA
        $valor = $dados['valor'];

        if (is_string($valor)) {
            $valor = str_replace(['R$', ' ', '.'], '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        if (!is_numeric($valor)) {
            throw new \InvalidArgumentException('Valor inválido para movimentação de caixa.');
        }

        $valor = (float) $valor;

        DB::table('movimentacoes_caixa')->insert([
            'caixa_id'          => $dados['caixa_id'],
            'user_id'           => $dados['user_id'],
            'tipo'              => $dados['tipo'],
            'valor'             => $valor,
            'forma_pagamento'   => $dados['forma_pagamento'] ?? null,
            'bandeira'          => $dados['bandeira'] ?? null,
            'origem_id'         => $dados['origem_id'] ?? $dados['caixa_id'],
            'observacao'        => $dados['observacao'] ?? null,
            'data_movimentacao' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);


        \Log::info('Movimentação de caixa registrada', [
            'caixa_id' => $dados['caixa_id'],
            'tipo'     => $dados['tipo'],
            'valor'    => $dados['valor']
        ]);
    }

    /**
     * Retorna o total de entradas em DINHEIRO do caixa
     * Fonte: pagamentos_venda
     */
    public static function totalEntradasDinheiro(int $caixaId): float
    {
        return (float) DB::table('pagamentos_venda')
            ->where('caixa_id', $caixaId)
            ->where('status', 'confirmado')
            ->where('forma_pagamento', 'dinheiro')
            ->sum('valor');
    }

    /**
     * Retorna total de entradas manuais (sobra)
     */
    public static function totalEntradasManuais(int $caixaId): float
    {
        return (float) DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->where('tipo', 'entrada_manual')
            ->sum('valor');
    }

        /**
     * Retorna o total de entradas do caixa (exceto fundo de troco)
     */
    public static function totalEntradas(int $caixaId): float
    {
        return
             self::totalEntradasManuais($caixaId)
            + self::totalEntradasDinheiro($caixaId);

            
    }

    /**
     * Retorna total de saídas do caixa
     */
    public static function totalSaidas(int $caixaId): float
    {
        return (float) DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['saida_manual'])
            ->sum('valor');
    }

    /**
     * Calcula o saldo esperado do caixa (DINHEIRO)
     */
    public static function total_esperado (int $caixaId): float
    {
        $caixa = Caixa::findOrFail($caixaId);

        return
            (float) $caixa->fundo_troco
            // + self::totalEntradasManuais($caixaId)
            + self::totalEntradasDinheiro($caixaId)
            - self::totalSaidas($caixaId);
    }

     /**
     * Retorna o total do sistema por forma de pagamento para um caixa
     * Exemplo de retorno:
     * [
     *   'dinheiro' => 1000,
     *   'pix' => 500,
     *   'carteira' => 500,
     *   'cartao_debito' => 596,
     *   'cartao_credito' => 1000,
     *   'visa' => 200,
     *   'mastercard' => 200,
     *   ...
     * ]
     */
    public static function totaisPorForma($caixaId)
    {
        $caixa = Caixa::with(['vendas.pagamentos'])->findOrFail($caixaId);

        $totais = [];

        foreach ($caixa->vendas as $venda) {
            foreach ($venda->pagamentos as $pag) {
                if ($pag->status !== 'confirmado') {
                    continue;
                }

                $forma = $pag->forma_pagamento;

                // Se for cartão, separar por bandeira
                if ($forma === 'cartao' && !empty($pag->bandeira)) {
                    $bandeira = strtolower($pag->bandeira);
                    $totais[$bandeira] = ($totais[$bandeira] ?? 0) + $pag->valor;
                } else {
                    $totais[$forma] = ($totais[$forma] ?? 0) + $pag->valor;
                }
            }
        }

        return $totais;
    }

    /**
     * Calcula divergências entre o total informado e o total do sistema
     * Retorna array ['forma' => diferença]
     */
    public static function calcularDivergencias($caixaId)
    {
        $totaisSistema = self::totaisPorForma($caixaId);

        $caixa = Caixa::with(['movimentacoes'])->findOrFail($caixaId);

        // Considera apenas entradas físicas informadas
        $totaisInformados = [];

        foreach ($caixa->movimentacoes as $mov) {
            if ($mov->tipo === 'entrada_manual' || $mov->tipo === 'abertura') {
                $forma = $mov->forma_pagamento ?? 'dinheiro';
                $totaisInformados[$forma] = ($totaisInformados[$forma] ?? 0) + $mov->valor;
            }
        }

        $divergencias = [];
        foreach ($totaisSistema as $forma => $valorSistema) {
            $valorInformado = $totaisInformados[$forma] ?? 0;
            $dif = $valorInformado - $valorSistema;
            if ($dif != 0) {
                $divergencias[$forma] = $dif;
            }
        }

        return $divergencias;
    }
}
