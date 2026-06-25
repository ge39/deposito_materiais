<?php

namespace App\Services;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Orcamento;

use App\Models\ItemOrcamento;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\Cliente;
use App\Models\Produto;
use App\Services\EstoqueService;
use App\Enums\TipoMovimentacao;
use App\Enums\OrigemMovimentacao;


class OrcamentoService
{
    protected EstoqueService $estoqueService;

    public function __construct(EstoqueService $estoqueService)
    {
        $this->estoqueService = $estoqueService;
    }

    /* =========================================
     | LISTAGEM
     ========================================= */
    public function listar($request)
    {
        $query = Orcamento::with('cliente');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->codigo_orcamento) {
            $query->where('codigo_orcamento', $request->codigo_orcamento);
        }

        return $query->orderByDesc('id')->paginate(15);
    }

    // public function listar($request)
    // {
    //     $query = Orcamento::with('cliente')
    //         ->whereIn('status', [
    //             'Aprovado',
    //             'Aguardando Aprovacao',
    //             'Aguardando Estoque'
                
    //         ]);

    //     if ($request->status) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->codigo_orcamento) {
    //         $query->where('codigo_orcamento', $request->codigo_orcamento);
    //     }

    //     return $query
    //         ->orderByDesc('id')
    //         ->paginate(15)
    //         ->withQueryString();
    // }
    /* =========================================
     | DADOS CREATE
     ========================================= */
    public function dadosParaCriacao()
    {
        return [
            // Busca todos os clientes ordenados por nome
            'clientes' => Cliente::orderBy('nome')->get(),

            'produtos' => Produto::with([
                'lotes' => function ($q) {

                    $q->where('status', 1) // Apenas lotes ativos

                    // Garante que há estoque disponível (quantidade - reservado > 0)
                    ->whereRaw('(quantidade - quantidade_reservada) > 0')

                    // Regra principal: controle de validade depende do produto
                    ->where(function ($q2) {

                        // 🔹 CASO 1: Produto controla validade (controla_validade = 1)
                        $q2->where(function ($q3) {

                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 1);
                            })

                            // Só traz lotes dentro da validade
                            ->whereDate('validade_lote', '>=', now());

                            // Se quiser considerar lotes sem validade como válidos, descomente:
                            // ->orWhereNull('validade_lote');
                        })

                        // 🔹 CASO 2: Produto NÃO controla validade (controla_validade = 0)
                        ->orWhere(function ($q3) {

                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 0);
                            });

                            // Aqui NÃO aplicamos nenhum filtro de validade
                            // Ou seja, todos os lotes entram independentemente da data
                        });
                    })

                    // Ordena os lotes pelo ID (mais antigos primeiro)
                    ->orderBy('id', 'asc');
                }
            ])

            // Ordena os produtos pelo nome
            ->orderBy('nome')->get(),
        ];
    }

   
    // public function criarCompleto(array $request)
    // {
    //     return DB::transaction(function () use ($request) {

    //         $empresa = Empresa::where('ativo', 1)->firstOrFail();

    //         $orcamento = Orcamento::create([
    //             'cliente_id' => $request['cliente_id'],
    //             'empresa_id' => $empresa->id,
    //             'data_orcamento' => now(),
    //             'validade' => $request['validade'],
    //             'codigo_orcamento' => now()->format('YmdHis'),
    //             'status' => 'Aguardando Aprovacao',
    //             'observacoes' => $request['observacoes'] ?? null,
    //             'total' => 0,
    //             'ativo' => 1,
    //             'editando_por' => Auth::id(),
    //             'editando_em' => now(),
    //         ]);

    //         // 🔥 garante código único
    //         $orcamento->update([
    //             'codigo_orcamento' => now()->format('YmdHis') . $orcamento->id
    //         ]);

    //         if (empty($request['produtos']) || !is_array($request['produtos'])) {
    //             throw new \Exception('Produtos inválidos no orçamento');
    //         }

    //         foreach ($request['produtos'] as $itemReq) {

    //             $produtoId = $itemReq['id'] ?? null;
    //             if (!$produtoId) continue;

    //             $qtd = $itemReq['quantidade_solicitada']
    //                 ?? $itemReq['quantidade']
    //                 ?? 0;

    //             $preco = $itemReq['preco_unitario']
    //                 ?? $itemReq['preco']
    //                 ?? 0;

    //             if ($qtd <= 0) continue;

    //             $item = ItemOrcamento::create([
    //                 'orcamento_id' => $orcamento->id,
    //                 'produto_id' => $produtoId,
    //                 'quantidade_solicitada' => $qtd,
    //                 'quantidade_atendida' => 0,
    //                 'quantidade_pendente' => $qtd,
    //                 'preco_unitario' => $preco,
    //                 'subtotal' => $qtd * $preco,
    //                 'status' => 'indisponivel',
    //                 'previsao_entrega' => now()->addDays(7),
    //             ]);

    //             // 🔥 1. processa estoque (FIFO)
    //             $this->estoqueService->recalcularReservar(
    //                 $item->id,
    //                 $produtoId,
    //                 $qtd
    //             );

    //             // 🔥 2. garante consistência do item
    //             $this->recalcularItemCompleto($item);

    //             // 🔥 3. atualiza estado em memória
    //             $item->refresh();
    //         }

    //         // 🔥 4. recarrega TODOS os itens já atualizados
    //         $orcamento->load('itens');

    //         // 🔥 5. calcula total CORRETAMENTE (baseado no atendido)
    //         $total = $orcamento->itens->sum(function ($item) {
    //             return $item->quantidade_atendida * $item->preco_unitario;
    //         });

    //         // 🔥 6. verifica pendências reais
    //         $temPendente = $orcamento->itens
    //             ->where('quantidade_pendente', '>', 0)
    //             ->isNotEmpty();

    //         // 🔥 7. atualiza orçamento FINAL
    //         $orcamento->update([
    //             'total' => $total,
    //             'status' => $temPendente
    //                 ? 'Aguardando Estoque'
    //                 : 'Aguardando Aprovacao'
    //         ]);

    //         return $orcamento;
    //     });
    // }

    public function criarCompleto(array $request)
    {
        return DB::transaction(function () use ($request) {

            $empresa = Empresa::where('ativo', 1)->firstOrFail();

            // 🚀 1. Captura o desconto global enviado pela tela (Ex: 5)
            $descontoGlobal = (float) ($request['desconto_global'] ?? 0);

            $orcamento = Orcamento::create([
                'cliente_id' => $request['cliente_id'],
                'empresa_id' => $empresa->id,
                'data_orcamento' => now(),
                'validade' => $request['validade'],
                'codigo_orcamento' => now()->format('YmdHis'),
                'status' => 'Aguardando Aprovacao',
                'observacoes' => $request['observacoes'] ?? null,
                'total' => 0, // Inicia zerado para receber o cálculo real abaixo
                'ativo' => 1,
                'editando_por' => Auth::id(),
                'editando_em' => now(),
            ]);

            $orcamento->update([
                'codigo_orcamento' => now()->format('YmdHis') . $orcamento->id
            ]);

            if (empty($request['produtos']) || !is_array($request['produtos'])) {
                throw new \Exception('Produtos inválidos no orçamento');
            }

            foreach ($request['produtos'] as $itemReq) {
                $produtoId = $itemReq['id'] ?? null;
                if (!$produtoId) continue;

                $qtd = $itemReq['quantidade_solicitada'] ?? $itemReq['quantidade'] ?? 0;
                $preco = $itemReq['preco_unitario'] ?? $itemReq['preco'] ?? 0;

                if ($qtd <= 0) continue;

                // 🧠 MATEMÁTICA DO RATEIO EXIBIDO NA SUA IMAGEM
                // Exemplo com Desconto de 5%
                $valorDescontoUnitario = $preco * ($descontoGlobal / 100); 
                $precoUnitarioLiquido = $preco - $valorDescontoUnitario;   
                $valorDescontoTotalItem = $qtd * $valorDescontoUnitario;   
                $subtotalItem = $qtd * $precoUnitarioLiquido;              

                if ($subtotalItem < 0) $subtotalItem = 0;

                // Salva cada item contendo sua respectiva fatia do desconto proporcional
                $item = ItemOrcamento::create([
                    'orcamento_id'          => $orcamento->id,
                    'produto_id'            => $produtoId,
                    'quantidade_solicitada' => $qtd,
                    'quantidade_atendida'   => 0,
                    'quantidade_pendente'   => $qtd,
                    'preco_unitario'        => $preco,                
                    'preco_liquido'         => $precoUnitarioLiquido, 
                    'desconto_percentual'   => $descontoGlobal,       // Grava os 5% na linha
                    'valor_desconto'        => $valorDescontoTotalItem, // Grava os R$ 261,75 rateados nas linhas
                    'subtotal'              => $subtotalItem,         // Subtotal Líquido da linha
                    'status'                => 'indisponivel',
                    'previsao_entrega'      => now()->addDays(7),
                ]);

                // Executa suas rotinas nativas de estoque de forma transparente
                $this->estoqueService->recalcularReservar($item->id, $produtoId, $qtd);
                $this->recalcularItemCompleto($item);
                $item->refresh();
            }

            // Recarrega as linhas processadas do banco
            $orcamento->load('itens');

            // 🚀 2. SELEÇÃO DA REGRA DE FECHAMENTO DO TOTAL DO ORÇAMENTO:
            // Se o total deve ser estrito ao valor que fechou na tela (Solicitado), usamos a linha abaixo:
            $totalLiquidoFinal = $orcamento->itens->sum('subtotal');

            // Verifica se o service de estoque deixou pendências de saldo
            $temPendente = $orcamento->itens
                ->where('quantidade_pendente', '>', 0)
                ->isNotEmpty();

            // 🚀 3. Atualiza o orçamento gravando os R$ 4973,25 finais na coluna 'total'
            $orcamento->update([
                'total' => $totalLiquidoFinal,
                'status' => $temPendente ? 'Aguardando Estoque' : 'Aguardando Aprovacao'
            ]);

            return $orcamento;
        });
    }
     /* =========================================
     | EDITAR
     ========================================= */
   
    // public function dadosParaEdicao($id)
    // {
    //     try {

    //         $orcamento = Orcamento::with([
    //             'itens.produto.unidadeMedida',
    //             'itens.produto.lotes' => function ($q) {

    //                 $q->where('status', 1)
    //                 ->whereRaw('(quantidade - quantidade_reservada) > 0')
    //                 ->where(function ($q2) {

    //                     // 🔹 Produto controla validade
    //                     $q2->where(function ($q3) {
    //                         $q3->whereHas('produto', function ($p) {
    //                             $p->where('controla_validade', 1);
    //                         })
    //                         ->where(function ($q4) {
    //                             $q4->whereDate('validade_lote', '>=', now())
    //                                 ->orWhereNull('validade_lote');
    //                         });
    //                     })

    //                     // 🔹 Produto NÃO controla validade
    //                     ->orWhere(function ($q3) {
    //                         $q3->whereHas('produto', function ($p) {
    //                             $p->where('controla_validade', 0);
    //                         });
    //                     });
    //                 })
    //                 ->orderBy('id', 'asc');
    //             },
    //             'itens.lote'
    //         ])->findOrFail($id);

    //         // 🔒 Controle de edição concorrente
    //         if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
    //             $usuario = $orcamento->usuarioEditando;
    //             $nomeUsuario = $usuario->name ?? 'Outro usuário';

    //             return [
    //                 'erro' => "Este orçamento está sendo editado por: {$nomeUsuario}"
    //             ];
    //         }

    //         $orcamento->update([
    //             'editando_por' => auth()->id(),
    //             'editando_em' => now()
    //         ]);

    //         // 👥 Clientes
    //         $clientes = Cliente::where('ativo', 1)
    //             ->orderBy('nome')
    //             ->get();

    //         // 📦 Produtos já usados no orçamento
    //         $produtosIdsOrcamento = $orcamento->itens->pluck('produto_id');

    //         // 📦 Produtos disponíveis + usados
    //         $produtos = Produto::with([
    //             'unidadeMedida',
    //             'lotes' => function ($q) {

    //                 $q->where('status', 1)
    //                 ->whereRaw('(quantidade - quantidade_reservada) > 0')
    //                 ->where(function ($q2) {

    //                     // 🔹 Produto controla validade
    //                     $q2->where(function ($q3) {
    //                         $q3->whereHas('produto', function ($p) {
    //                             $p->where('controla_validade', 1);
    //                         })
    //                         ->where(function ($q4) {
    //                             $q4->whereDate('validade_lote', '>=', now())
    //                                 ->orWhereNull('validade_lote');
    //                         });
    //                     })

    //                     // 🔹 Produto NÃO controla validade
    //                     ->orWhere(function ($q3) {
    //                         $q3->whereHas('produto', function ($p) {
    //                             $p->where('controla_validade', 0);
    //                         });
    //                     });

    //                 })
    //                 ->orderBy('id', 'asc');
    //             }
    //         ])
    //         ->where('ativo', 1)
    //         ->where(function ($q) use ($produtosIdsOrcamento) {

    //             // Produtos com estoque válido
    //             $q->where(function ($q2) {
    //                 $q2->where('controla_validade', 0)
    //                 ->orWhereHas('lotes', function ($qq) {
    //                     $qq->where('status', 1)
    //                         ->whereRaw('(quantidade - quantidade_reservada) > 0')
    //                         ->where(function ($q3) {
    //                             $q3->whereDate('validade_lote', '>=', now())
    //                                 ->orWhereNull('validade_lote');
    //                         });
    //                 });
    //             })

    //             // OU produtos já usados no orçamento
    //             ->orWhereIn('id', $produtosIdsOrcamento);
    //         })
    //         ->orderBy('nome')
    //         ->get();

    //         // 🔗 Mapear lotes por produto
    //         $lotes = [];

    //         foreach ($produtos as $produto) {
    //             $lotes[$produto->id] = $produto->lotes;
    //         }

    //         return compact('orcamento', 'clientes', 'produtos', 'lotes');

    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

    //         return [
    //             'erro' => 'Orçamento não encontrado',
    //             'detalhes' => $e->getMessage()
    //         ];

    //     } catch (\Illuminate\Database\QueryException $e) {

    //         return [
    //             'erro' => 'Erro de banco de dados',
    //             'sql' => $e->getSql(),
    //             'bindings' => $e->getBindings(),
    //             'mensagem' => $e->getMessage()
    //         ];

    //     } catch (\Throwable $e) {

    //         return [
    //             'erro' => 'Erro inesperado',
    //             'mensagem' => $e->getMessage(),
    //             'arquivo' => $e->getFile(),
    //             'linha' => $e->getLine(),
    //             'trace' => collect($e->getTrace())->take(5)
    //         ];
    //     }
    // }

    public function dadosParaEdicao($id)
    {
        try {
            $orcamento = Orcamento::with([
                'itens.produto.unidadeMedida',
                'itens.produto.lotes' => function ($q) {
                    $q->where('status', 1)
                    ->whereRaw('(quantidade - quantidade_reservada) > 0')
                    ->where(function ($q2) {
                        // 🔹 Produto controla validade
                        $q2->where(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 1);
                            })
                            ->where(function ($q4) {
                                $q4->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                        })
                        // 🔹 Produto NÃO controla validade
                        ->orWhere(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 0);
                            });
                        });
                    })
                    ->orderBy('id', 'asc');
                },
                'itens.lote'
            ])->findOrFail($id);

            // 🔒 Controle de edição concorrente
            if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
                $usuario = $orcamento->usuarioEditando;
                $nomeUsuario = $usuario->name ?? 'Outro usuário';

                return [
                    'erro' => "Este orçamento está sendo editado por: {$nomeUsuario}"
                ];
            }

            $orcamento->update([
                'editando_por' => auth()->id(),
                'editando_em' => now()
            ]);

            // 👥 Clientes
            $clientes = Cliente::where('ativo', 1)
                ->orderBy('nome')
                ->get();

            // 📦 Produtos já usados no orçamento
            $produtosIdsOrcamento = $orcamento->itens->pluck('produto_id');

            // 📦 Produtos disponíveis + usados
            $produtos = Produto::with([
                'unidadeMedida',
                'lotes' => function ($q) {
                    $q->where('status', 1)
                    ->whereRaw('(quantidade - quantidade_reservada) > 0')
                    ->where(function ($q2) {
                        // 🔹 Produto controla validade
                        $q2->where(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 1);
                            })
                            ->where(function ($q4) {
                                $q4->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                        })
                        // 🔹 Produto NÃO controla validade
                        ->orWhere(function ($q3) {
                            $q3->whereHas('produto', function ($p) {
                                $p->where('controla_validade', 0);
                            });
                        });
                    })
                    ->orderBy('id', 'asc');
                }
            ])
            ->where('ativo', 1)
            ->where(function ($q) use ($produtosIdsOrcamento) {
                // Produtos com estoque válido
                $q->where(function ($q2) {
                    $q2->where('controla_validade', 0)
                    ->orWhereHas('lotes', function ($qq) {
                        $qq->where('status', 1)
                            ->whereRaw('(quantidade - quantidade_reservada) > 0')
                            ->where(function ($q3) {
                                $q3->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                    });
                })
                // OU produtos já usados no orçamento
                ->orWhereIn('id', $produtosIdsOrcamento);
            })
            ->orderBy('nome')
            ->get();

            // 🔗 Mapear lotes por produto
            $lotes = [];
            foreach ($produtos as $produto) {
                $lotes[$produto->id] = $produto->lotes;
            }

            // 📊 CONFORMIDADE COM DESCONTO GLOBAL:
            // Garante que se o campo de desconto global estiver nulo no banco, ele retorne 0 para a Blade
            if (!isset($orcamento->desconto_global)) {
                $orcamento->desconto_global = $orcamento->desconto_percentual ?? 0;
            }

            // Calcula o Total Bruto (Soma pura de item * quantidade) para enviar separado caso o $orcamento->total já seja o valor líquido no seu banco
            $totalBrutoCalculado = $orcamento->itens->sum(function($item) {
                return $item->quantidade_solicitada * $item->preco_unitario;
            });

            return compact('orcamento', 'clientes', 'produtos', 'lotes', 'totalBrutoCalculado');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'erro' => 'Orçamento não encontrado',
                'detalhes' => $e->getMessage()
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            return [
                'erro' => 'Erro de banco de dados',
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'mensagem' => $e->getMessage()
            ];
        } catch (\Throwable $e) {
            return [
                'erro' => 'Erro inesperado',
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)
            ];
        }
    }

    /* =========================================
     | APROVAR COMPLETO
     ========================================= */
   
    public function aprovarCompleto(int $orcamentoId)
    {
        return DB::transaction(function () use ($orcamentoId) {

            $movService = app(MovimentacaoOrcamentoService::class);

            $orcamento = Orcamento::findOrFail($orcamentoId);

            if ($orcamento->status === 'Aprovado') {
                throw new \Exception('Orçamento já aprovado');
            }

            foreach ($orcamento->itens as $item) {

                $vinculos = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($vinculos as $v) {

                    $lote = DB::table('lotes')
                        ->where('id', $v->lote_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$lote) continue;

                    $disponivel = $lote->quantidade - $lote->quantidade_reservada;

                    if ($disponivel <= 0) continue;

                    $atender = min($item->quantidade_pendente, $disponivel);

                    if ($atender > 0) {

                        // 🔹 estoque continua funcionando normal
                        DB::table('lotes')
                            ->where('id', $v->lote_id)
                            ->increment('quantidade_reservada', $atender);

                        DB::table('item_orcamento_lotes')
                            ->where('id', $v->id)
                            ->increment('quantidade_atendida', $atender);

                        $item->quantidade_atendida += $atender;
                        $item->quantidade_pendente -= $atender;
                    }
                }

                $item->status = $item->quantidade_pendente > 0
                    ? 'parcial'
                    : 'disponivel';

                $item->save();
            }

            $temPendente = $orcamento->itens()
                ->where('quantidade_pendente', '>', 0)
                ->exists();

            $statusFinal = $temPendente ? 'Aguardando Estoque' : 'Aprovado';

            $orcamento->update([
                'status' => $statusFinal
            ]);

            // 🔥 REGRA CORRETA: só registra se aprovado
            if ($statusFinal === 'Aprovado') {

                foreach ($orcamento->itens as $item) {

                    $vinculos = DB::table('item_orcamento_lotes')
                        ->where('item_orcamento_id', $item->id)
                        ->get();

                    foreach ($vinculos as $v) {

                        $lote = DB::table('lotes')
                            ->where('id', $v->lote_id)
                            ->first();

                        if (!$lote) continue;

                        $antes = $lote->quantidade_reservada;
                        $depois = $lote->quantidade_reservada;

                        $movService->registrar(
                            $v->lote_id,
                            $orcamento->id,
                            $item->id,
                            TipoMovimentacao::APROVADO,
                            $antes,
                            $depois,
                            'Orçamento aprovado',
                            OrigemMovimentacao::SISTEMA
                        );
                    }
                }
            }

            return $orcamento;
        });
    }

    // public function recalcularItemCompleto(ItemOrcamento $item, ?float $quantidadeSolicitada = null): void
    // {
    //     DB::transaction(function () use ($item, $quantidadeSolicitada) {

    //         $item = ItemOrcamento::lockForUpdate()->find($item->id);

    //         // 🔹 Atualiza quantidade solicitada (se vier do request)
    //         if (!is_null($quantidadeSolicitada)) {
    //             $item->quantidade_solicitada = $quantidadeSolicitada;
    //         }

    //         // 🔹 Recalcula atendido (fonte da verdade = lotes)
    //         $quantidadeAtendida = DB::table('item_orcamento_lotes')
    //             ->where('item_orcamento_id', $item->id)
    //             ->sum('quantidade_reservada');

    //         $item->quantidade_atendida = $quantidadeAtendida;

    //         // 🔹 Calcula pendente
    //         $item->quantidade_pendente =
    //             max(0, $item->quantidade_solicitada - $quantidadeAtendida);

    //         // 🔹 Status correto
    //         if ($quantidadeAtendida <= 0) {
    //             $item->status = 'indisponivel';
    //         } elseif ($item->quantidade_pendente > 0) {
    //             $item->status = 'parcial';
    //         } else {
    //             $item->status = 'disponivel';
    //         }

    //         $item->save();
    //     });
    // }
    
    public function recalcularItemCompleto(ItemOrcamento $item, ?float $quantidadeSolicitada = null): void
    {
        DB::transaction(function () use ($item, $quantidadeSolicitada) {

            $item = ItemOrcamento::lockForUpdate()->find($item->id);

            // 🔹 Atualiza quantidade solicitada (se vier do request)
            if (!is_null($quantidadeSolicitada)) {
                $item->quantidade_solicitada = $quantidadeSolicitada;
            }

            // 🔹 Recalcula atendido (fonte da verdade = lotes)
            $quantidadeAtendida = DB::table('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id)
                ->sum('quantidade_reservada');

            $item->quantidade_atendida = $quantidadeAtendida;

            // 🔹 Calcula pendente
            $item->quantidade_pendente =
                max(0, $item->quantidade_solicitada - $quantidadeAtendida);

            // 🔹 Status correto
            if ($quantidadeAtendida <= 0) {
                $item->status = 'indisponivel';
            } elseif ($item->quantidade_pendente > 0) {
                $item->status = 'parcial';
            } else {
                $item->status = 'disponivel';
            }

            // =======================================================================
            // 🚀 SEGUNDA OPÇÃO DE REGRA FINANCEIRA: CÁLCULO ATÔMICO DO DESCONTO E SUB-TOTAL
            // =======================================================================
            $qtd = (float) $item->quantidade_solicitada;
            $precoUnitario = (float) $item->preco_unitario;
            
            // Recupera a porcentagem inteira que salvamos no create através do model mapeado
            $descPercent = (int) ($item->desconto_percentual ?? 0);

            // 1. Calcula o montante bruto total sem os abatimentos
            $totalBrutoItem = $qtd * $precoUnitario;

            // 2. Calcula o valor em reais do desconto unitário
            $valorDescontoUnitario = $precoUnitario * ($descPercent / 100);

            // 3. Calcula o total em reais economizado na linha inteira (Quantidade x Desconto Unitário)
            $valorDescontoTotalItem = $qtd * $valorDescontoUnitario;

            // 4. Subtotal líquido: Subtrai o desconto total do bruto acumulado
            $subtotalLiquido = $totalBrutoItem - $valorDescontoTotalItem;
            if ($subtotalLiquido < 0) $subtotalLiquido = 0;

            // 5. Preço unitário líquido que será usado pelo PDV no faturamento posterior
            $precoUnitarioLiquido = $precoUnitario - $valorDescontoUnitario;

            // Alimenta as propriedades do objeto na memória antes do disparo do SQL
            $item->preco_liquido = $precoUnitarioLiquido;
            $item->desconto_percentual = $descPercent;
            $item->valor_desconto = $valorDescontoTotalItem;
            $item->subtotal = $subtotalLiquido; // 🎯 Sobrescreve o subtotal com o valor líquido exato com descontos!
            // =======================================================================

            $item->save();
        });
    }

    public function atualizarCompleto(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $orcamento = Orcamento::with('itens')->lockForUpdate()->findOrFail($id);

            $produtos = collect($request->input('produtos', []));
            $total = 0;

            // ===============================
            // 🔥 1. REMOVER ITENS QUE NÃO VIERAM NO REQUEST
            // ===============================
            $produtos = collect($produtos);
            $produtosIdsRequest = $produtos->pluck('id')->toArray();

            $itensRemover = $orcamento->itens()
                ->whereNotIn('produto_id', $produtosIdsRequest)
                ->get();

            // foreach ($itensRemover as $item) {

            //     // 🔓 libera reservas
            //    $this->estoqueService->reservar(
            //     $item->id,
            //     $produtoId,
            //     $quantidadeNova
            // );
            //     $item->delete();
            // }

            foreach ($itensRemover as $item) {

                // 🔓 1. libera estoque reservado desse item
                $this->estoqueService->cancelarReserva($item);

                // 🗑️ remove o item do orçamento
                $item->delete();
            }

            // ===============================
            // 🔥 2. PROCESSAR ITENS DO REQUEST
            // ===============================
            foreach ($produtos as $produtoReq) {

                $produtoId = $produtoReq['id'] ?? null;
                $loteId = $produtoReq['lote_id'] ?? null;

                $quantidadeNova = $produtoReq['quantidade_solicitada']
                    ?? $produtoReq['quantidade']
                    ?? 0;

                $preco = $produtoReq['preco_unitario'] ?? 0;

                if (!$produtoId || $quantidadeNova <= 0) {
                    continue;
                }

                // 🔍 busca ou cria item
                $item = ItemOrcamento::firstOrCreate(
                    [
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $produtoId,
                    ],
                    [
                        'quantidade_solicitada' => 0,
                        'quantidade_atendida' => 0,
                        'quantidade_pendente' => 0,
                        'preco_unitario' => $preco,
                        'subtotal' => 0,
                        'status' => 'indisponivel',
                        'previsao_entrega' => now()->addDays(7),
                        'ativo' => true,
                    ]
                );

                // ===============================
                // 🔥 ATUALIZA QUANTIDADE SOLICITADA
                // ===============================
                $item->quantidade_solicitada = $quantidadeNova;
                $item->preco_unitario = $preco;
                $item->save();

                // ===============================
                // 🔥 LIBERA TODAS AS RESERVAS ATUAIS
                // (RECALCULA DO ZERO - EVITA BUG)
                // ===============================
                $this->estoqueService->cancelarReserva($item);

                // ===============================
                // 🔥 TENTA ATENDER NOVAMENTE (FIFO)
                // ===============================
                $this->estoqueService->recalcularReservar(
                    $item->id,
                    $produtoId,
                    $quantidadeNova
                );

                // 🔄 garante estado atualizado
                $item->refresh();

                // ===============================
                // 🔥 RECALCULO FINAL DO ITEM
                // ===============================
                $quantidadeAtendida = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->sum('quantidade_reservada');

                $item->quantidade_atendida = $quantidadeAtendida;
                $item->quantidade_pendente =
                    max(0, $item->quantidade_solicitada - $quantidadeAtendida);

                // 🔥 status correto
                if ($quantidadeAtendida <= 0) {
                    $item->status = 'indisponivel';
                } elseif ($item->quantidade_pendente > 0) {
                    $item->status = 'parcial';
                } else {
                    $item->status = 'disponivel';
                }

                // 🔥 subtotal baseado no solicitado (RECOMENDADO)
                $item->subtotal = $item->quantidade_solicitada * $item->preco_unitario;

                $item->save();

                $total += $item->subtotal;
            }

            // ===============================
            // 🔥 RECALCULO FINAL DO TOTAL (GARANTIA)
            // ===============================
            $total = ItemOrcamento::where('orcamento_id', $orcamento->id)
                ->selectRaw('SUM(quantidade_solicitada * preco_unitario) as total')
                ->value('total') ?? 0;

            // ===============================
            // 🔥 STATUS FINAL DO ORÇAMENTO
            // ===============================
            $temPendentes = ItemOrcamento::where('orcamento_id', $orcamento->id)
                ->whereRaw('(quantidade_solicitada - quantidade_atendida) > 0')
                ->exists();

            $orcamento->update([
                'total' => $total,
                'status' => $temPendentes
                    ? 'Aguardando Estoque'
                    : 'Aguardando Aprovacao',
            ]);

            return $orcamento;
        });
    }

    //  public function atualizarCompleto(Request $request, $id)
    // {
    //     return DB::transaction(function () use ($request, $id) {

    //         $orcamento = Orcamento::with('itens')->lockForUpdate()->findOrFail($id);

    //         // 🚀 CAPTURA O PERCENTUAL DE DESCONTO GLOBAL VINDO DO RODAPÉ DA TELA
    //         $descontoGlobal = (float) $request->input('desconto_global', 0);

    //         $produtos = collect($request->input('produtos', []));
    //         $totalBrutoAcumulado = 0;

    //         // ===============================
    //         // 🔥 1. REMOVER ITENS QUE NÃO VIERAM NO REQUEST
    //         // ===============================
    //         $produtosIdsRequest = $produtos->pluck('id')->toArray();

    //         $itensRemover = $orcamento->itens()
    //             ->whereNotIn('produto_id', $produtosIdsRequest)
    //             ->get();

    //         foreach ($itensRemover as $item) {
    //             // 🔓 1. libera estoque reservado desse item
    //             $this->estoqueService->cancelarReserva($item);

    //             // 🗑️ remove o item do orçamento
    //             $item->delete();
    //         }

    //         // ===============================
    //         // 🔥 2. PROCESSAR ITENS DO REQUEST
    //         // ===============================
    //         // foreach ($produtos as $produtoReq) {

    //         //     $produtoId = $produtoReq['id'] ?? null;
    //         //     $loteId = $produtoReq['lote_id'] ?? null;

    //         //     $quantidadeNova = $produtoReq['quantidade_solicitada']
    //         //         ?? $produtoReq['quantidade']
    //         //         ?? 0;

    //         //     $preco = (float) ($produtoReq['preco_unitario'] ?? 0);

    //         //     if (!$produtoId || $quantidadeNova <= 0) {
    //         //         continue;
    //         //     }

    //         //     // 🔍 busca ou cria item
    //         //     $item = ItemOrcamento::firstOrCreate(
    //         //         [
    //         //             'orcamento_id' => $orcamento->id,
    //         //             'produto_id' => $produtoId,
    //         //         ],
    //         //         [
    //         //             'quantidade_solicitada' => 0,
    //         //             'quantidade_atendida' => 0,
    //         //             'quantidade_pendente' => 0,
    //         //             'preco_unitario' => $preco,
    //         //             'preco_liquido' => $preco,
    //         //             'desconto_percentual' => 0,
    //         //             'valor_desconto' => 0,
    //         //             'subtotal' => 0,
    //         //             'status' => 'indisponivel',
    //         //             'previsao_entrega' => now()->addDays(7),
    //         //             'ativo' => true,
    //         //         ]
    //         //     );

    //         //     // 🧠 MATEMÁTICA DO RATEIO DO DESCONTO GLOBAL DO RODAPÉ
    //         //     $valorDescontoUnitario = $preco * ($descontoGlobal / 100);
    //         //     $precoUnitarioLiquido = $preco - $valorDescontoUnitario;
    //         //     $valorDescontoTotalItem = $quantidadeNova * $valorDescontoUnitario;
    //         //     $subtotalLiquidoItem = $quantidadeNova * $precoUnitarioLiquido;

    //         //     if ($subtotalLiquidoItem < 0) {
    //         //         $subtotalLiquidoItem = 0;
    //         //     }

    //         //     // ===============================
    //         //     // 🔥 ATUALIZA QUANTIDADE, PREÇO E CÁLCULO LÍQUIDO
    //         //     // ===============================
    //         //     $item->quantidade_solicitada = $quantidadeNova;
    //         //     $item->preco_unitario        = $preco;
    //         //     $item->preco_liquido         = $precoUnitarioLiquido;  // 🎯 Atualizado: Preço cobrado abatido o desconto
    //         //     $item->desconto_percentual   = $descontoGlobal;        // 🎯 Atualizado: Histórico de % da tela
    //         //     $item->valor_desconto        = $valorDescontoTotalItem;// 🎯 Atualizado: Total de R$ economizados na linha
    //         //     $item->subtotal              = $subtotalLiquidoItem;   // 🎯 Atualizado: Guarda subtotal já líquido no banco
    //         //     $item->save();

    //         //     // ===============================
    //         //     // 🔥 LIBERA TODAS AS RESERVAS ATUAIS (MANTIDO SEU PADRÃO)
    //         //     // ===============================
    //         //     $this->estoqueService->cancelarReserva($item);

    //         //     // ===============================
    //         //     // 🔥 TENTA ATENDER NOVAMENTE (FIFO)
    //         //     // ===============================
    //         //     $this->estoqueService->recalcularReservar(
    //         //         $item->id,
    //         //         $produtoId,
    //         //         $quantidadeNova
    //         //     );

    //         //     // 🔄 garante estado atualizado
    //         //     $item->refresh();

    //         //     // ===============================
    //         //     // 🔥 RECALCULO FINAL DO ITEM
    //         //     // ===============================
    //         //     $quantidadeAtendida = DB::table('item_orcamento_lotes')
    //         //         ->where('item_orcamento_id', $item->id)
    //         //         ->sum('quantidade_reservada');

    //         //     $item->quantidade_atendida = $quantidadeAtendida;
    //         //     $item->quantidade_pendente = max(0, $item->quantidade_solicitada - $quantidadeAtendida);

    //         //     // 🔥 status correto
    //         //     if ($quantidadeAtendida <= 0) {
    //         //         $item->status = 'indisponivel';
    //         //     } elseif ($item->quantidade_pendente > 0) {
    //         //         $item->status = 'parcial';
    //         //     } else {
    //         //         $item->status = 'disponivel';
    //         //     }

    //         //     $item->save();
    //         // }

    //                     // ===============================
    //         // 🔥 2. PROCESSAR ITENS DO REQUEST
    //         // ===============================
    //         // Captura o desconto vindo do campo único do rodapé da tela
    //         $descontoGlobal = (int) $request->input('desconto_global', 0);

    //         foreach ($produtos as $produtoReq) {

    //             $produtoId = $produtoReq['id'] ?? null;
    //             $loteId = $produtoReq['lote_id'] ?? null;

    //             $quantidadeNova = $produtoReq['quantidade_solicitada']
    //                 ?? $produtoReq['quantidade']
    //                 ?? 0;

    //             $preco = (float) ($produtoReq['preco_unitario'] ?? 0);

    //             if (!$produtoId || $quantidadeNova <= 0) {
    //                 continue;
    //             }

    //             // 🔍 busca ou cria item
    //             $item = ItemOrcamento::firstOrCreate(
    //                 [
    //                     'orcamento_id' => $orcamento->id,
    //                     'produto_id' => $produtoId,
    //                 ],
    //                 [
    //                     'quantidade_solicitada' => 0,
    //                     'quantidade_atendida' => 0,
    //                     'quantidade_pendente' => 0,
    //                     'preco_unitario' => $preco,
    //                     'preco_liquido' => $preco,
    //                     'desconto_percentual' => 0,
    //                     'valor_desconto' => 0,
    //                     'subtotal' => 0,
    //                     'status' => 'indisponivel',
    //                     'previsao_entrega' => now()->addDays(7),
    //                     'ativo' => true,
    //                 ]
    //             );

    //             // 🧮 CÁLCULO ALINHADO COM SEUS DADOS REAIS:
    //             $valorDescontoUnitario = $preco * ($descontoGlobal / 100);
    //             $precoLiquidoItem      = $preco - $valorDescontoUnitario;
    //             $valorDescontoTotal    = $quantidadeNova * $valorDescontoUnitario;
    //             $subtotalLiquido       = $quantidadeNova * $precoLiquidoItem;

    //             // ===============================
    //             // 🔥 ATUALIZA QUANTIDADE E VALORES LÍQUIDOS
    //             // ===============================
    //             $item->quantidade_solicitada = $quantidadeNova;
    //             $item->preco_unitario        = $preco;
    //             $item->preco_liquido         = $precoLiquidoItem;    // Salva ex: 99.75
    //             $item->desconto_percentual   = $descontoGlobal;      // Salva ex: 5
    //             $item->valor_desconto        = $valorDescontoTotal;    // Salva ex: 21.00
    //             $item->subtotal              = $subtotalLiquido;       // Salva ex: 399.00
    //             $item->save();

    //             // ===============================
    //             // 🔥 LIBERA TODAS AS RESERVAS ATUAIS (MANTIDO SEU PADRÃO)
    //             // ===============================
    //             $this->estoqueService->cancelarReserva($item);

    //             // ===============================
    //             // 🔥 TENTA ATENDER NOVAMENTE (FIFO)
    //             // ===============================
    //             $this->estoqueService->recalcularReservar(
    //                 $item->id,
    //                 $produtoId,
    //                 $quantidadeNova
    //             );

    //             // 🔄 garante estado atualizado
    //             $item->refresh();

    //             // ===============================
    //             // 🔥 RECALCULO FINAL DO ITEM
    //             // ===============================
    //             $quantidadeAtendida = DB::table('item_orcamento_lotes')
    //                 ->where('item_orcamento_id', $item->id)
    //                 ->sum('quantidade_reservada');

    //             $item->quantidade_atendida = $quantidadeAtendida;
    //             $item->quantidade_pendente = max(0, $item->quantidade_solicitada - $quantidadeAtendida);

    //             if ($quantidadeAtendida <= 0) {
    //                 $item->status = 'indisponivel';
    //             } elseif ($item->quantidade_pendente > 0) {
    //                 $item->status = 'parcial';
    //             } else {
    //                 $item->status = 'disponivel';
    //             }

    //             // 🎯 ATENÇÃO: Mantém o subtotal como líquido baseado no que foi calculado acima
    //             $item->subtotal = $subtotalLiquido;
    //             $item->save();
    //         }

    //         // ===============================
    //         // 🔥 RECALCULO FINAL DO TOTAL (SOMA OS SUBTOTAIS LÍQUIDOS)
    //         // ===============================
    //         $totalLiquidoOrcamento = ItemOrcamento::where('orcamento_id', $orcamento->id)
    //             ->sum('subtotal') ?? 0;

    //         // ===============================
    //         // 🔥 STATUS FINAL DO ORÇAMENTO
    //         // ===============================
    //         $temPendentes = ItemOrcamento::where('orcamento_id', $orcamento->id)
    //             ->whereRaw('(quantidade_solicitada - quantity_atendida) > 0')
    //             ->exists();

    //         $orcamento->update([
    //             'total'  => $totalLiquidoOrcamento, // Grava ex: 731.50
    //             'status' => $temPendentes ? 'Aguardando Estoque' : 'Aguardando Aprovacao',
    //         ]);

    //         return $orcamento;

    //         // ===============================
    //         // 🔥 RECALCULO FINAL DO TOTAL (BASEADO NO ATENDIDO LÍQUIDO OU SOLICITADO LÍQUIDO)
    //         // Se o caixa cobrar pelo Solicitado Líquido (R$ 4973,25 da sua imagem), usamos SUM(subtotal)
    //         // ===============================
    //         $totalLiquidoFinal = ItemOrcamento::where('orcamento_id', $orcamento->id)
    //             ->sum('subtotal') ?? 0;

    //         // ===============================
    //         // 🔥 STATUS FINAL DO ORÇAMENTO
    //         // ===============================
    //         $temPendentes = ItemOrcamento::where('orcamento_id', $orcamento->id)
    //             ->whereRaw('(quantidade_solicitada - quantidade_atendida) > 0')
    //             ->exists();

    //         $orcamento->update([
    //             'total'  => $totalLiquidoFinal, // 🎯 Salva o valor líquido correto com desconto
    //             'status' => $temPendentes ? 'Aguardando Estoque' : 'Aguardando Aprovacao',
    //         ]);

    //         return $orcamento;
    //     });
    // }


    public function cancelar(Orcamento $orcamento)
    {
        DB::transaction(function () use ($orcamento) {

            // 🔒 evita cancelar duas vezes
            if ($orcamento->status === 'Cancelado') {
                return;
            }

            // 🔄 percorre itens e libera reservas
            foreach ($orcamento->itens as $item) {
                $this->estoqueService->cancelarReserva($item);
            }

            // 🧾 atualiza status
            $orcamento->update([
                'status' => 'Cancelado'
            ]);
        });

        return $orcamento;
    }
   
    public function gerarPdfCompleto(Orcamento $orcamento)
    {
        $orcamento->load([
            'cliente',
            'itens.produto.unidadeMedida',
            'itens.lotes' // ou itens.lotes.lote
        ]);

        return Pdf::loadView('orcamentos.pdf', compact('orcamento'));
    }

    /* =========================================
     | WHATSAPP
     ========================================= */
    public function enviarWhatsapp(Orcamento $orcamento)
    {
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));

        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $path = storage_path("app/public/orcamento/{$fileName}");

        $pdf->save($path);

        $telefone = preg_replace('/\D/', '', $orcamento->cliente->telefone ?? '');

        if (!$telefone) {
            throw new \Exception("Cliente sem telefone.");
        }

        $link = asset("storage/orcamento/{$fileName}");
        $msg = urlencode("Olá! Segue seu orçamento: {$link}");

        return "https://wa.me/55{$telefone}?text={$msg}";
    }

    /* =========================================
     | VISUALIZAR PDF
     ========================================= */
    public function visualizarArquivo(Orcamento $orcamento)
    {
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        return asset("storage/orcamento/{$fileName}");
    }
        /* =========================================
     | FATURAR ORÇAMENTO NO PDV (CAIXA)
     ========================================= */
    /**
     * Converte os saldos lógicos de reserva do balcão em baixa física real.
     * Desenvolvido para alta performance em redes de múltiplos PDVs.
     */
    public function faturarEfetivo(Orcamento $orcamento, array $dados)
    {
        // Executa todo o bloco sob uma transação isolada para segurança concorrente
        DB::transaction(function () use ($orcamento, $dados) {
            
            // Re-carrega os itens travando para atualização no banco (Locking)
            foreach ($orcamento->itens as $item) {
                
                // Busca os registros associados na tabela pivot intermediária
                $vinculosLotes = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->get();

                foreach ($vinculosLotes as $v) {
                    
                    // 🔴 GARGALO EVITADO: Baixa atômica nativa diretamente no banco de dados.
                    // Subtrai a reserva lógica e o estoque físico real do lote simultaneamente.
                    DB::table('lotes')
                        ->where('id', $v->lote_id)
                        ->update([
                            'quantidade_reservada' => DB::raw("quantidade_reservada - {$v->quantidade_reservada}"),
                            'quantidade'           => DB::raw("quantidade - {$v->quantidade_reservada}"),
                            'quantidade_disponivel' => DB::raw("quantidade - quantidade_reservada")
                        ]);

                    // Converte o status do vínculo de reservado para atendido de fato
                    DB::table('item_orcamento_lotes')
                        ->where('id', $v->id)
                        ->update([
                            'quantidade_atendida'  => $v->quantidade_reservada,
                            'quantidade_reservada' => 0
                        ]);
                }

                // Sincroniza o estado interno do item do orçamento
                $item->update([
                    'quantidade_atendida' => $item->quantidade_solicitada,
                    'quantidade_pendente' => 0,
                    'status'              => 'disponivel',
                ]);

                // Registra o log histórico da linha do tempo da mercadoria
                \App\Models\MovimentacaoOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'item_orcamento_id' => $item->id,
                    'tipo' => 'Faturamento',
                    'descricao' => "Venda finalizada no caixa do PDV. " . floatval($item->quantidade_solicitada) . " un. liberadas.",
                    'quantidade' => $item->quantidade_solicitada,
                    'user_id' => Auth::id() ?? $orcamento->editando_por,
                ]);
            }

            // Atualiza o cabeçalho definitivo do documento para bloquear acessos concorrentes na rede
            $orcamento->update([
                'status'       => 'Faturado',
                'editando_por' => Auth::id(),
                'editando_em'  => now(),
            ]);
        });

        return $orcamento;
    }

}