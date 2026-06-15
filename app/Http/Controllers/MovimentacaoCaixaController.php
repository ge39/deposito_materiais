<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

   /**
     * Registra saídas manuais (despesas, ajustes, etc.) em transação.
     */
    public function registrarSaidasManuais(Caixa $caixa, Request $request): void
    {
        $request->validate([
            'despesas'        => 'nullable|numeric|min:0.01',
            'ajuste_negativo' => 'nullable|numeric|min:0.01',
            'outras_saidas'   => 'nullable|numeric|min:0.01',
        ]);

        $mapaSaidas = [
            'despesas'        => 'despesa',
            'ajuste_negativo' => 'ajuste_negativo',
            'outras_saidas'   => 'outras_saidas',
        ];

        DB::transaction(function () use ($caixa, $request, $mapaSaidas) {
            foreach ($mapaSaidas as $inputName => $enumTipo) {
                if ($valor = $request->input($inputName)) {
                    MovimentacaoCaixa::create([
                        'caixa_id'          => $caixa->id,
                        'user_id'           => auth()->id(),
                        'tipo'              => $enumTipo,
                        'valor'             => $valor,
                        'observacao'        => "Saída manual: $inputName",
                        'data_movimentacao' => now(),
                    ]);
                }
            }
        });
    }
    /**
     * Armazena novas saídas de caixa garantindo os princípios ACID e isolamento de PDV.
     */
    public function store(Request $request)
    {
        $request->validate([
            'caixa_id'        => 'required|integer|exists:caixas,id',
            'despesas'        => 'nullable|numeric|min:0.01',
            'ajuste_negativo' => 'nullable|numeric|min:0.01',
            'outras_saidas'   => 'nullable|numeric|min:0.01',
        ]);

        $mapaSaidas = [
            'despesas'        => 'despesa',
            'ajuste_negativo' => 'ajuste_negativo',
            'outras_saidas'   => 'outras_saidas',
        ];

        try {
            // [ATOMICIDADE & ISOLAMENTO] Iniciando a transação ACID
            DB::transaction(function () use ($request, $mapaSaidas) {
                
                // [ISOLAMENTO] lockForUpdate() bloqueia o registro do caixa para evitar concorrência (Race Conditions)
                $caixaAtivo = Caixa::where('id', $request->caixa_id)
                    ->where('status', 'aberto') // [CONSISTÊNCIA] Valida estado do caixa
                    ->lockForUpdate() 
                    ->first();

                if (!$caixaAtivo) {
                    throw new \Exception('O caixa informado não está aberto ou está inacessível no momento.');
                }

                foreach ($mapaSaidas as $inputName => $enumTipo) {
                    $valor = $request->input($inputName);

                    if ($valor && $valor > 0) {
                        MovimentacaoCaixa::create([
                            'caixa_id'          => $caixaAtivo->id,
                            'user_id'           => auth()->id(), // Identificação do operador do PDV
                            'tipo'              => $enumTipo,
                            'valor'             => $valor,
                            'forma_pagamento'   => 'dinheiro',
                            'observacao'        => 'Saída de caixa local via PDV (' . $inputName . ').',
                            'data_movimentacao' => now(),
                        ]);
                    }
                }
            }); // Se qualquer comando falhar aqui dentro, o Laravel executa o Rollback automático.

            return redirect()->back()->with('success', 'Saídas de caixa registradas no PDV com total segurança!');

        } catch (\Exception $e) {
            // Captura falhas ou travas de concorrência retornando um erro limpo para a interface do PDV
            return redirect()->back()->withErrors(['error' => 'Falha ao processar operação financeira: ' . $e->getMessage()]);
        }
    }

    /**
     * Atualiza os lançamentos de saídas vigentes em ambiente multi-caixas isolado.
     */
    public function update(Request $request, $caixaId)
    {
        $request->validate([
            'despesas'        => 'nullable|numeric|min:0',
            'ajuste_negativo' => 'nullable|numeric|min:0',
            'outras_saidas'   => 'nullable|numeric|min:0',
        ]);

        $mapaSaidas = [
            'despesas'        => 'despesa',
            'ajuste_negativo' => 'ajuste_negativo',
            'outras_saidas'   => 'outras_saidas',
        ];

        try {
            // [ATOMICIDADE] Garante a execução completa ou rollback total
            DB::transaction(function () use ($request, $caixaId, $mapaSaidas) {
                
                // [ISOLAMENTO & CONSISTÊNCIA] Bloqueia o registro do caixa contra alterações externas simultâneas
                $caixaAtivo = Caixa::where('id', $caixaId)
                    ->where('status', 'aberto')
                    ->lockForUpdate()
                    ->first();

                if (!$caixaAtivo) {
                    throw new \Exception('O caixa para edição está fechado ou inválido.');
                }

                foreach ($mapaSaidas as $inputName => $enumTipo) {
                    $valor = $request->input($inputName, 0);

                    // Busca o lançamento travando o registro específico para modificação
                    $movimentacao = MovimentacaoCaixa::where('caixa_id', $caixaAtivo->id)
                        ->where('tipo', $enumTipo)
                        ->lockForUpdate()
                        ->first();

                    if ($valor > 0) {
                        if ($movimentacao) {
                            $movimentacao->update([
                                'valor'      => $valor,
                                'observacao' => 'Saída local corrigida sob isolamento de dados no PDV.',
                            ]);
                        } else {
                            MovimentacaoCaixa::create([
                                'caixa_id'          => $caixaAtivo->id,
                                'user_id'           => auth()->id(),
                                'tipo'              => $enumTipo,
                                'valor'             => $valor,
                                'forma_pagamento'   => 'dinheiro',
                                'data_movimentacao' => now(),
                            ]);
                        }
                    } else {
                        if ($movimentacao) {
                            $movimentacao->delete(); // Remove o registro caso o operador zere o valor do campo
                        }
                    }
                }
            });

            return redirect()->back()->with('success', 'Lançamentos de caixa atualizados com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Inconsistência ao atualizar os dados: ' . $e->getMessage()]);
        }
    }

    /**
     * Registra saídas manuais fracionadas em múltiplos caixas via painel gerencial.
     */
    // public function registrarSaidaLote(Request $request)
    // {
    //     // 1. Validação básica da estrutura recebida via AJAX
    //     $request->validate([
    //         'finalidade' => 'required|string|max:255',
    //         'rateio'     => 'required|array|min:1',
    //         'rateio.*.caixa_id' => 'required|integer|exists:caixas,id',
    //         'rateio.*.valor'    => 'required|numeric|min:0.01',
    //     ]);

    //     try {
    //         // [ACID: ATOMICIDADE] Tudo ou nada. Se um caixa falhar, desfaz todos os lançamentos do lote.
    //         DB::transaction(function () use ($request) {
                
    //             foreach ($request->rateio as $item) {
                    
    //                 // [ACID: ISOLAMENTO] lockForUpdate previne concorrência com o operador na ponta do PDV
    //                 $caixa = Caixa::where('id', $item['caixa_id'])
    //                     ->where('status', 'banco_aberto', 'aberto') // Garante consistência do estado
    //                     ->lockForUpdate()
    //                     ->first();

    //                 if (!$caixa) {
    //                     throw new \Exception("O caixa #{$item['caixa_id']} não está mais disponível ou foi fechado.");
    //                 }

    //                 // Cria o lançamento da saída física usando o ENUM correto da sua tabela
    //                 MovimentacaoCaixa::create([
    //                     'caixa_id'          => $caixa->id,
    //                     'user_id'           => auth()->id(), // ID do Gerente/Operador responsável que autorizou no painel
    //                     'tipo'              => 'saida_manual', // Define como saída manual / despesa local
    //                     'valor'             => $item['valor'],
    //                     'forma_pagamento'   => 'dinheiro',
    //                     'observacao'        => 'Saída gerencial automatizada: ' . $request->finalidade,
    //                     'data_movimentacao' => now(),
    //                 ]);
    //             }
    //         });

    //         return response()->json(['success' => true, 'message' => 'Lançamentos financeiros realizados com sucesso nos caixas afetados!']);

    //     } catch (\Exception $e) {
    //         // Retorna o erro HTTP 422 para o JavaScript capturar e alertar na tela
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    //     }
    // }

    /**
     * Exibe a página visual do painel gerencial (Lado Esquerdo e Direito)
     */
    public function painelGerencialSaidas()
    {
        // Busca todos os caixas com status aberto na rede trazendo o operador
        $caixasAbertos = Caixa::with(['usuario']) 
            ->where('status', 'aberto')
            ->get()
            ->map(function ($caixa) {
                
                // ACID: Soma entradas em dinheiro (Abertura, Vendas, Entradas Manuais)
                $entradasDinheiro = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                    ->whereIn('tipo', ['abertura', 'venda', 'entrada', 'entrada_manual'])
                    ->where('forma_pagamento', 'dinheiro')
                    ->sum('valor');

                // ACID: Soma saídas em dinheiro (Despesas, Sangrias, Ajustes, Saídas Manuais)
                $saidasDinheiro = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                    ->whereIn('tipo', ['despesa', 'sangria', 'ajuste_negativo', 'outras_saidas', 'saida_manual'])
                    ->sum('valor');

                // Define dinamicamente a propriedade usada no Javascript da Blade
                $caixa->saldo_dinheiro_atual = $entradasDinheiro - $saidasDinheiro;

                return $caixa;
            });

        // Certifique-se de que o caminho do arquivo blade seja esse (mude caso use outra pasta)
        return view('gerencia.painel_saidas', compact('caixasAbertos'));
    }

    /**
     * Processa a gravação automática do lote rateado disparado pelo AJAX
     */
    public function registrarSaidaLote(Request $request)
    {
        $request->validate([
            'finalidade' => 'required|string|max:255',
            'rateio'     => 'required|array|min:1',
            'rateio.*.caixa_id' => 'required|integer|exists:caixas,id',
            'rateio.*.valor'    => 'required|numeric|min:0.01',
        ]);

        try {
            // [ACID: ATOMICIDADE] Rollback automático em caso de qualquer exceção
            DB::transaction(function () use ($request) {
                foreach ($request->rateio as $item) {
                    
                    // [ACID: ISOLAMENTO] lockForUpdate previne Race Conditions no PDV
                    $caixa = Caixa::where('id', $item['caixa_id'])
                        ->where('status', 'aberto')
                        ->lockForUpdate()
                        ->first();

                    if (!$caixa) {
                        throw new \Exception("O caixa #{$item['caixa_id']} não está mais ativo ou foi fechado.");
                    }

                    MovimentacaoCaixa::create([
                        'caixa_id'          => $caixa->id,
                        'user_id'           => auth()->id(), 
                        'tipo'              => 'saida_manual', 
                        'valor'             => $item['valor'],
                        'forma_pagamento'   => 'dinheiro',
                        'observacao'        => 'Saída gerencial automatizada: ' . $request->finalidade,
                        'data_movimentacao' => now(),
                    ]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Lançamentos concluídos com sucesso!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Exibe o histórico de saídas gerenciais para conferência e reimpressão.
     */
    public function historicoSaidas()
    {
        // Busca as movimentações do tipo saida_manual trazendo os dados do caixa e do usuário
        $saidas = MovimentacaoCaixa::with(['caixa.usuario'])
            ->where('tipo', 'saida_manual')
            ->orderBy('data_movimentacao', 'desc')
            ->paginate(15); // Paginação para não pesar a rede

        return view('gerencia.historico_saidas', compact('saidas'));
    }



}   

