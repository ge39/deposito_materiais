<?php

namespace App\Services;
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
use Illuminate\Http\Request;

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
                $this->estoqueService->reservar(
                    $item->id,
                    $produtoId,
                    $qtd
                );

                $total += $item->subtotal;
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
   public function dadosParaEdicao($id)
    {
        try {

            $orcamento = Orcamento::with([
                'itens.produto.unidadeMedida',
                'itens.produto.lotes' => function ($q) {
                    $q->where('status', 1)
                        ->whereRaw('(quantidade - quantidade_reservada) > 0')
                        ->where(function ($q2) {
                            $q2->whereDate('validade_lote', '>=', now())
                                ->orWhereNull('validade_lote');
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

            // 👥 Clientes ativos
            $clientes = Cliente::where('ativo', 1)
                ->orderBy('nome')
                ->get();

            // 📦 Produtos do orçamento
            $produtosIdsOrcamento = $orcamento->itens->pluck('produto_id');

            $produtos = Produto::with([
                'unidadeMedida',
                'lotes' => function ($query) {
                    $query->where('status', 1)
                        ->whereRaw('(quantidade - quantidade_reservada) > 0')
                        ->where(function ($q) {
                            $q->whereDate('validade_lote', '>=', now())
                                ->orWhereNull('validade_lote');
                        })
                        ->orderBy('id', 'asc');
                }
            ])
            ->where('ativo', 1)
            ->where(function ($query) use ($produtosIdsOrcamento) {
                $query->where(function ($q) {
                    $q->where('controla_validade', 0)
                        ->orWhereHas('lotes', function ($qq) {
                            $qq->where('status', 1)
                                ->whereRaw('(quantidade - quantidade_reservada) > 0')
                                ->where(function ($qqq) {
                                    $qqq->whereDate('validade_lote', '>=', now())
                                        ->orWhereNull('validade_lote');
                                });
                        });
                })
                ->orWhereIn('id', $produtosIdsOrcamento);
            })
            ->orderBy('nome')
            ->get();

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

    // public function atualizarCompleto(Request $request, $id)
    // {
        
    //    $orcamento = Orcamento::with('itens')->findOrFail($id);
    //     $estoqueService = app(EstoqueService::class);

    //     DB::transaction(function () use ($request, $orcamento, $estoqueService) {

    //     try {
    //         // 🔥 1. CANCELA RESERVAS ANTIGAS (CORRETO)
    //         foreach ($orcamento->itens as $item) {
    //             $estoqueService->cancelarReserva($item);
    //         }

    //         // 🔥 2. REMOVE ITENS ANTIGOS
    //         $orcamento->itens()->delete();

    //         $total = 0;

    //         // 🔥 3. GARANTE QUE EXISTE ARRAY
    //         $produtos = $request->input('produtos', []);

    //         foreach ($produtos as $produtoReq) {

    //             // 🔥 4. NORMALIZA DADOS (ANTI-ERRO)
    //             $produtoId = $produtoReq['id'] ?? null;

    //             $quantidade = $produtoReq['quantidade_solicitada']
    //                 ?? $produtoReq['quantidade']
    //                 ?? $produtoReq['qtd']
    //                 ?? 0;

    //             $preco = $produtoReq['preco_unitario']
    //                 ?? $produtoReq['preco']
    //                 ?? 0;

    //             // 🔥 5. VALIDAÇÃO MÍNIMA (evita lixo no banco)
    //             if (!$produtoId || $quantidade <= 0) {
    //                 continue;
    //             }

    //             // 🔥 6. CRIA ITEM (SEM RESERVA MANUAL)
    //             $item = ItemOrcamento::create([
    //                 'orcamento_id' => $orcamento->id,
    //                 'produto_id' => $produtoId,
    //                 'quantidade_solicitada' => $quantidade,
    //                 'quantidade_atendida' => 0,
    //                 'quantidade_pendente' => $quantidade,
    //                 'preco_unitario' => $preco,
    //                 'subtotal' => 0,
    //                 'status' => 'indisponivel',
    //                 'previsao_entrega' => now()->addDays(7),
    //                 'ativo' => true,
    //                 'observacoes' => $request->observacoes ?? null,
    //             ]);

    //             // 🔥 7. RESERVA VIA SERVICE (REGRA CENTRAL)
    //             $estoqueService->reservar(
    //                 $item->id,
    //                 $produtoId,
    //                 $quantidade
    //             );

    //             // 🔥 8. RECALCULA COM BASE NO QUE FOI REALMENTE ATENDIDO
    //             $item->refresh();

    //             $item->subtotal = $item->quantidade_atendida * $preco;
    //             $item->save();

    //             $total += $item->subtotal;
    //         }

    //         // 🔥 9. STATUS FINAL DO ORÇAMENTO
    //         $temPendentes = $orcamento->itens()
    //             ->where('quantidade_pendente', '>', 0)
    //             ->exists();

    //         $orcamento->update([
    //             'total' => $total,
    //             'status' => $temPendentes
    //                 ? 'Aguardando Estoque'
    //                 : 'Aguardando Aprovacao',
    //         ]);
                
    //          } catch (\Throwable $e) {

    //             return back()->with([
    //                 'erro' => 'Erro ao atualizar orçamento',
    //                 'mensagem' => $e->getMessage(),
    //                 'linha' => $e->getLine()
    //             ]);
    //         }

    //         });
      

    //     return redirect()
    //         ->route('orcamentos.index')
    //         ->with('success', 'Orçamento atualizado com sucesso!');
    // }

    public function atualizarCompleto(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $orcamento = Orcamento::with('itens')->findOrFail($id);

            $produtos = $request->input('produtos', []);
            $total = 0;

            foreach ($produtos as $produtoReq) {

                $produtoId = $produtoReq['id'] ?? null;
                $loteId = $produtoReq['lote_id'] ?? null;

                $quantidadeNova = $produtoReq['quantidade_solicitada']
                    ?? $produtoReq['quantidade']
                    ?? 0;

                $preco = $produtoReq['preco_unitario'] ?? 0;

                if (!$produtoId || !$loteId || $quantidadeNova <= 0) {
                    continue;
                }

                // 🔍 BUSCA ITEM EXISTENTE PELO PRODUTO
                $item = ItemOrcamento::where('orcamento_id', $orcamento->id)
                    ->where('produto_id', $produtoId)
                    ->first();

                if ($item) {

                    // 🔥 DIFERENÇA ENTRE O NOVO E O ANTIGO
                    $quantidadeAntiga = $item->quantidade_solicitada;
                    $diferenca = $quantidadeNova - $quantidadeAntiga;

                    // ===============================
                    // 🔥 SE DIMINUIU → LIBERA ESTOQUE
                    // ===============================
                    if ($diferenca < 0) {

                        $reduzir = abs($diferenca);

                        $vinculos = DB::table('item_orcamento_lotes')
                            ->where('item_orcamento_id', $item->id)
                            ->orderByDesc('id') // LIFO para devolver
                            ->get();

                        foreach ($vinculos as $v) {

                            if ($reduzir <= 0) break;

                            $lote = Lote::lockForUpdate()->find($v->lote_id);
                            if (!$lote) continue;

                            $remover = min($reduzir, $v->quantidade_reservada);

                            // 🔥 DEVOLVE ESTOQUE
                            $lote->decrement('quantidade_reservada', $remover);

                            // 🔥 ATUALIZA VÍNCULO
                            DB::table('item_orcamento_lotes')
                                ->where('id', $v->id)
                                ->update([
                                    'quantidade_reservada' => DB::raw("quantidade_reservada - {$remover}"),
                                    'quantidade_atendida' => DB::raw("quantidade_atendida - {$remover}")
                                ]);

                            $item->quantidade_atendida -= $remover;
                            $reduzir -= $remover;
                        }
                    }

                    // ===============================
                    // 🔥 SE AUMENTOU → RESERVA MAIS
                    // ===============================
                    if ($diferenca > 0) {
                        $this->estoqueService->reservar(
                            $item->id,
                            $produtoId,
                            $diferenca
                        );
                    }

                    // 🔥 ATUALIZA QUANTIDADE (AGORA FUNCIONA)
                    $item->update([
                        'quantidade_solicitada' => $quantidadeNova,
                        'quantidade_pendente' => max(0, $quantidadeNova - $item->quantidade_atendida),
                        'preco_unitario' => $preco,
                    ]);

                } else {

                    // ===============================
                    // 🔥 INSERT NOVO ITEM
                    // ===============================
                    $item = ItemOrcamento::create([
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $produtoId,
                        'quantidade_solicitada' => $quantidadeNova,
                        'quantidade_atendida' => 0,
                        'quantidade_pendente' => $quantidadeNova,
                        'preco_unitario' => $preco,
                        'subtotal' => 0,
                        'status' => 'indisponivel',
                        'previsao_entrega' => now()->addDays(7),
                        'ativo' => true,
                    ]);

                    $this->estoqueService->reservar(
                        $item->id,
                        $produtoId,
                        $quantidadeNova
                    );
                }

                // 🔥 RECALCULA
                $item->refresh();

                $item->subtotal = $item->quantidade_atendida * $preco;
                $item->save();

                $total += $item->subtotal;
            }

            // ===============================
            // 🔥 STATUS FINAL
            // ===============================
            $temPendentes = $orcamento->itens()
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