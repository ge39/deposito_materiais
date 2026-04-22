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

    /* =========================================
     | CRIAR COMPLETO
     ========================================= */
    public function criarCompleto(array $request)
    {
        return DB::transaction(function () use ($request) {

            $empresa = Empresa::where('ativo', 1)->firstOrFail();

            $orcamento = Orcamento::create([
                'cliente_id' => $request['cliente_id'],
                'empresa_id' => $empresa->id,
                'data_orcamento' => now(),
                'validade' => $request['validade'],
                'codigo_orcamento' => now()->format('YmdHis'),
                'status' => 'Aguardando Aprovacao',
                'observacoes' => $request['observacoes'] ?? null,
                'total' => 0,
                'ativo' => 1,
                'editando_por' => Auth::id(),
                'editando_em' => now(),
            ]);

            $orcamento->update([
                'codigo_orcamento' => now()->format('YmdHis') . $orcamento->id
            ]);

            $total = 0;

            if (empty($request['produtos']) || !is_array($request['produtos'])) {
                throw new \Exception('Produtos inválidos no orçamento');
            }

            foreach ($request['produtos'] as $itemReq) {

                $produtoId = $itemReq['id'] ?? null;
                if (!$produtoId) continue;

                $qtd = $itemReq['quantidade_solicitada']
                    ?? $itemReq['quantidade']
                    ?? 0;

                $preco = $itemReq['preco_unitario']
                    ?? $itemReq['preco']
                    ?? 0;

                if ($qtd <= 0) continue;

                $item = ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $produtoId,
                    'quantidade_solicitada' => $qtd,
                    'quantidade_atendida' => 0,
                    'quantidade_pendente' => $qtd,
                    'preco_unitario' => $preco,
                    'subtotal' => $qtd * $preco,
                    'status' => 'indisponivel',
                    'previsao_entrega' => now()->addDays(7),
                ]);

                // 🔥 ESTOQUE
                $this->estoqueService->recalcularReservar(
                    $item->id,
                    $produtoId,
                    $qtd
                );

                // $total += $item->subtotal;
                $total += $item->quantidade_atendida * $item->preco_unitario;
                $total = ItemOrcamento::where('orcamento_id', $orcamento->id)
                ->selectRaw('SUM(quantidade_atendida * preco_unitario) as total')
                ->value('total');
            }

            $temPendente = $orcamento->itens()
                ->whereIn('status', ['indisponivel', 'parcial'])
                ->exists();

            $orcamento->update([
                'total' => $total,
                'status' => $temPendente
                    ? 'Aguardando Estoque'
                    : 'Aguardando Aprovacao'
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
    //                     ->whereRaw('(quantidade - quantidade_reservada) > 0')
    //                     ->where(function ($q2) {
    //                         $q2->whereDate('validade_lote', '>=', now());
    //                             ->orWhereNull('validade_lote');
    //                     })
    //                     ->orderBy('id', 'asc');
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

    //         // 👥 Clientes ativos
    //         $clientes = Cliente::where('ativo', 1)
    //             ->orderBy('nome')
    //             ->get();

    //         // 📦 Produtos do orçamento
    //         $produtosIdsOrcamento = $orcamento->itens->pluck('produto_id');

    //         $produtos = Produto::with([
    //             'unidadeMedida',
    //             'lotes' => function ($query) {
    //                 $query->where('status', 1)
    //                     ->whereRaw('(quantidade - quantidade_reservada) > 0')
    //                     ->where(function ($q) {
    //                         $q->whereDate('validade_lote', '>=', now())
    //                             ->orWhereNull('validade_lote');
    //                     })
    //                     ->orderBy('id', 'asc');
    //             }
    //         ])
    //         ->where('ativo', 1)
    //         ->where(function ($query) use ($produtosIdsOrcamento) {
    //             $query->where(function ($q) {
    //                 $q->where('controla_validade', 0)
    //                     ->orWhereHas('lotes', function ($qq) {
    //                         $qq->where('status', 1)
    //                             ->whereRaw('(quantidade - quantidade_reservada) > 0')
    //                             ->where(function ($qqq) {
    //                                 $qqq->whereDate('validade_lote', '>=', now())
    //                                     ->orWhereNull('validade_lote');
    //                             });
    //                     });
    //             })
    //             ->orWhereIn('id', $produtosIdsOrcamento);
    //         })
    //         ->orderBy('nome')
    //         ->get();

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

            return compact('orcamento', 'clientes', 'produtos', 'lotes');

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

            $orcamento = Orcamento::findOrFail($orcamentoId);

            if ($orcamento->status === 'Aprovado') {
                throw new \Exception('Orçamento já aprovado');
            }

            foreach ($orcamento->itens as $item) {

                $lotes = DB::table('item_orcamento_lotes')
                    ->where('item_orcamento_id', $item->id)
                    ->get();

                foreach ($lotes as $l) {

                    $lote = Lote::lockForUpdate()->find($l->lote_id);
                    if (!$lote) continue;

                    $disponivel = $lote->quantidade - $lote->quantidade_reservada;

                    if ($disponivel <= 0) continue;

                    $atender = min($item->quantidade_pendente, $disponivel);

                    if ($atender > 0) {
                        $lote->quantidade_reservada += $atender;
                        $lote->save();

                        DB::table('item_orcamento_lotes')
                            ->where('id', $l->id)
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

            $orcamento->update([
                'status' => $temPendente ? 'Aguardando Estoque' : 'Aprovado'
            ]);

            return $orcamento;
        });
    }

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
}