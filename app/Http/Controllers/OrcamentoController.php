<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Lote;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrcamentoController extends Controller
{
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // 🔥 INICIO ALTERAÇÃO
    // private function distribuirLotes($produtoId, $quantidade)
    // {
    //     $produto = Produto::find($produtoId);

    //     $query = Lote::where('produto_id', $produtoId)
    //         ->where('status', 1)
    //         ->where('quantidade_disponivel', '>', 0)
    //         ->lockForUpdate();

    //     if ($produto->controla_validade) {
    //             // produtos com validade → FEFO
    //             $query->whereDate('validade_lote', '>=', now())
    //                 ->orderBy('validade_lote');
    //         } else {
    //             // 🔥 SEM validade → pega todos os lotes sem filtro
    //             $query->orderBy('id'); // ou FIFO simples
    //     }

    //     $lotes = $query->get();

    //     $resultado = [];

    //     foreach ($lotes as $lote) {
    //         if ($quantidade <= 0) break;

    //         $disponivel = $lote->quantidade_disponivel - ($lote->quantidade_reservada ?? 0);
    //         if ($disponivel <= 0) continue;

    //         $usar = min($disponivel, $quantidade);

    //         $resultado[] = [
    //             'lote' => $lote,
    //             'quantidade' => $usar
    //         ];

    //         $quantidade -= $usar;
    //     }

    //     if ($quantidade > 0) {
    //         throw new \Exception("Estoque insuficiente para o produto ID {$produtoId}");
    //     }

    //     return $resultado;
    // }

    private function distribuirLotes($produtoId, $quantidade)
    {
        $lotes = Lote::where('produto_id', $produtoId)
            ->whereRaw('quantidade > quantidade_reservada')
            ->orderBy('created_at') // FIFO (opcional)
            ->lockForUpdate()
            ->get();

        //     dd($lotes->map(function($l){
        //     return [
        //         'lote_id' => $l->id,
        //         'disponivel' => $l->quantidade - $l->quantidade_reservada
        //     ];
        // }));

        $distribuicao = [];
        $quantidadeRestante = $quantidade;

        foreach ($lotes as $lote) {

            $disponivel = $lote->quantidade - $lote->quantidade_reservada;

            if ($disponivel <= 0) continue;

            $qtd = min($disponivel, $quantidadeRestante);

            $distribuicao[] = [
                'lote' => $lote,
                'quantidade' => $qtd
            ];

            $quantidadeRestante -= $qtd;

            if ($quantidadeRestante <= 0) break;
        }

        return $distribuicao;
    }
    // 🔥 FIM ALTERAÇÃO

    /** LISTAGEM */
    public function index(Request $request)
    {
        $query = Orcamento::with('cliente');

        // 🔥 FILTRO DE STATUS
        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', [
                'Aguardando Aprovacao',
                'Aguardando Estoque'
            ]);
        }

        // 🔥 FILTRO POR CÓDIGO (INDEPENDENTE)
        if ($request->codigo_orcamento) {
            $query->where('codigo_orcamento', $request->codigo_orcamento);
        }

        $orcamentos = $query->orderBy('id', 'desc')->paginate(15);

        return view('orcamentos.index', compact('orcamentos'));
    }

    public function buscar(Request $request)
    {
        $orcamento = Orcamento::with([
            'cliente',
            'itens',
            'itens.produto.unidade_medida',
            'itens.lote'
        ])
        ->where('codigo', $request->codigo)
        ->first();

        return response()->json([
            'success' => true,
            'orcamento' => $orcamento
        ]);
    }

    public function create()
    {
        $clientes = Cliente::where('ativo', 'ativo')
            ->orderBy('nome')
            ->get();

         $filtroLotes = function ($query) {
            $query->where('status', 1)
                // ->where('quantidade_disponivel', '>', 0)
                ->whereRaw('quantidade > quantidade_reservada')
                ->where(function ($q) {

                    // 🔥 Produto NÃO controla validade → ignora filtro
                    $q->whereHas('produto', function ($p) {
                        $p->where('controla_validade', 0);
                    })

                    // 🔥 Produto controla validade → aplica regra
                    ->orWhere(function ($q2) {
                        $q2->whereHas('produto', function ($p) {
                            $p->where('controla_validade', 1);
                        })
                        ->where(function ($q3) {
                            $q3->whereDate('validade_lote', '>=', now())
                            ->orWhereNull('validade_lote');
                        });
                    });

                });
        };

        $produtos = Produto::with([
                'unidadeMedida',
                // 'lotes' => function ($query) use ($filtroLotes) {
                //     $filtroLotes($query);
                //     $query->orderBy('validade_lote');
                // }
                'lotes' => function ($query) {
                    $query->orderBy('validade_lote');
                }
            ])
            ->whereHas('lotes', $filtroLotes)
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        // $lotes = [];

        // foreach ($produtos as $produto) {
        //     if ($produto->lotes->isEmpty()) continue;

        //     $lotes[$produto->id] = $produto->lotes->map(function ($lote) {
        //         return [
        //             'id' => $lote->id,
        //             'numero_lote' => $lote->numero_lote,
        //             'quantidade_disponivel' => $lote->quantidade_disponivel,
        //             'validade_lote' => optional($lote->validade_lote)->format('Y-m-d'),
        //         ];
        //     })->values();
        // }

        $lotes = [];

        foreach ($produtos as $produto) {

            $lotesValidos = $produto->lotes->filter(function ($lote) {
                return $lote->status == 1 &&
                    $lote->quantidade_disponivel > 0 &&
                    (
                        !$lote->validade_lote ||
                        $lote->validade_lote >= now()
                    );
            })->values();

            $lotes[$produto->id] = [
                'controla_validade' => $produto->controla_validade,

                // 🔥 TODOS OS LOTES
                'todos' => $produto->lotes->map(function ($lote) {
                    return [
                        'id' => $lote->id,
                        'numero_lote' => $lote->numero_lote,
                        'quantidade_disponivel' => $lote->quantidade_disponivel,
                        'validade_lote' => $lote->validade_lote
                    ];
                })->values(),

                // 🔥 SOMENTE VÁLIDOS
                'validos' => $lotesValidos->map(function ($lote) {
                    return [
                        'id' => $lote->id,
                        'numero_lote' => $lote->numero_lote,
                        'quantidade_disponivel' => $lote->quantidade_disponivel,
                        'validade_lote' => $lote->validade_lote
                    ];
                })->values(),
            ];
        }

        return view('orcamentos.create', compact(
            'clientes',
            'produtos',
            'lotes'
        ));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'cliente_id' => 'required|exists:clientes,id',
    //         'data_orcamento' => 'required|date',
    //         'validade' => 'required|date|after_or_equal:data_orcamento',
    //         'produtos' => 'required|array|min:1',
    //         'produtos.*.id' => 'required|exists:produtos,id',
    //         'produtos.*.quantidade' => 'required|numeric|min:0.01',
    //         'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
    //         'produtos.*.lote_id' => 'required|exists:lotes,id',
    //     ]);

    //     DB::beginTransaction();

    //     try {

    //         $cliente = Cliente::where('id', $request->cliente_id)
    //             ->lockForUpdate()
    //             ->firstOrFail();

    //         $orcamento = Orcamento::create([
    //             'cliente_id' => $cliente->id,
    //             'data_orcamento' => $request->data_orcamento,
    //             'codigo_orcamento' => now()->format('YmdHis'),
    //             'validade' => $request->validade,
    //             'status' => 'Aguardando Aprovacao',
    //             'observacoes' => $request->observacoes,
    //             'total' => 0,
    //             'ativo' => 1,
    //         ]);

    //         $codigo = now()->format('Ymd') . $orcamento->id;
    //         $orcamento->update(['codigo_orcamento' => $codigo]);

    //         $total = 0;
    //         $produtoIds = [];

    //         foreach ($request->produtos as $produto) {

    //             if (in_array($produto['id'], $produtoIds)) {
    //                 throw new \Exception('Produto duplicado: ' . $produto['id']);
    //             }
    //             $produtoIds[] = $produto['id'];

    //             $produtoModel = Produto::where('id', $produto['id'])
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             $quantidade = (float) $produto['quantidade'];
    //             $preco = (float) $produto['preco_unitario'];

    //             // 🔥 INICIO ALTERAÇÃO
    //             $distribuicao = $this->distribuirLotes($produtoModel->id, $quantidade);

    //             foreach ($distribuicao as $d) {

    //                 $lote = $d['lote'];
    //                 $qtd = $d['quantidade'];

    //                 ItemOrcamento::create([
    //                     'orcamento_id' => $orcamento->id,
    //                     'produto_id' => $produtoModel->id,
    //                     'lote_id' => $lote->id,
    //                     'quantidade' => $qtd,
    //                     'quantidade_atendida' => $qtd,
    //                     'quantidade_pendente' => 0,
    //                     'status' => 'disponivel',
    //                     'preco_unitario' => $preco,
    //                     'subtotal' => $qtd * $preco,
    //                 ]);

    //                 $lote->quantidade_reservada += $qtd;
    //                 $lote->save();

    //                 $total += $qtd * $preco;
    //             }
    //             // 🔥 FIM ALTERAÇÃO
    //         }

    //         $orcamento->update([
    //             'total' => $total,
    //             'status' => 'Aguardando Aprovacao'
    //         ]);

    //         DB::commit();

    //         return redirect()->route('orcamentos.index')
    //             ->with('success', 'Orçamento criado com sucesso!');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withInput()->with('error', 'Erro ao criar orçamento: ' . $e->getMessage());
    //     }
    // }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_orcamento' => 'required|date',
            'validade' => 'required|date|after_or_equal:data_orcamento',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {

            $orcamento = Orcamento::create([
                'cliente_id' => $request->cliente_id,
                'data_orcamento' => $request->data_orcamento,
                'validade' => $request->validade,
                'codigo_orcamento' => now()->format('YmdHis'),
                'status' => 'Aguardando Aprovacao',
                'total' => 0,
                'ativo' => 1,
            ]);

            $total = 0;

            foreach ($request->produtos as $produto) {

                $quantidade = (float) $produto['quantidade'];
                $preco = (float) $produto['preco_unitario'];

                $quantidadeRestante = $quantidade;
                $quantidadeAtendida = 0;

                $lotes = Lote::where('produto_id', $produto['id'])
                    ->where('status', 1)
                    ->whereRaw('quantidade_disponivel > quantidade_reservada')
                    ->orderBy('created_at')
                    ->lockForUpdate()
                    ->get();

                foreach ($lotes as $lote) {

                    if ($quantidadeRestante <= 0) break;

                    $qtdReservada = $lote->reservar($quantidadeRestante);

                    if ($qtdReservada <= 0) continue;

                    ItemOrcamento::create([
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $produto['id'],
                        'lote_id' => $lote->id,
                        'quantidade' => $qtdReservada,
                        'quantidade_atendida' => $qtdReservada,
                        'quantidade_pendente' => 0,
                        'status' => 'disponivel',
                        'preco_unitario' => $preco,
                        'subtotal' => $qtdReservada * $preco,
                    ]);

                    $quantidadeRestante -= $qtdReservada;
                    $quantidadeAtendida += $qtdReservada;
                    $total += $qtdReservada * $preco;
                }
                   
                // 🔥 PENDENTE (REGRA DE NEGÓCIO)
                if ($quantidadeRestante > 0) {

                    ItemOrcamento::create([
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $produto['id'],
                        'lote_id' => $lote->id, // opcional: pode deixar null ou associar ao último lote tentado
                        'quantidade' => $quantidadeRestante,
                        'quantidade_atendida' => 0,
                        'quantidade_pendente' => $quantidadeRestante,
                        'status' => 'indisponivel',
                        'preco_unitario' => $preco,
                        'subtotal' => $quantidadeRestante * $preco,
                        'previsao_entrega' => now()->addDays(7),
                    ]);

                    $total += $quantidadeRestante * $preco;
                }
            }

            $orcamento->update([
                'total' => $total
            ]);

            DB::commit();

            return redirect()->route('orcamentos.index')
                ->with('success', 'Orçamento criado com sucesso!');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);

        DB::beginTransaction();

        try {

            // 🔥 INICIO ALTERAÇÃO (devolve estoque)
            foreach ($orcamento->itens as $item) {
                if ($item->lote_id) {
                    $lote = Lote::find($item->lote_id);
                    if ($lote) {
                        $lote->quantidade_reservada -= $item->quantidade_atendida ?? 0;
                        $lote->save();
                    }
                }
            }
            // 🔥 FIM ALTERAÇÃO

            $orcamento->itens()->delete();

            $total = 0;

            foreach ($request->produtos as $produto) {

                // 🔥 INICIO ALTERAÇÃO
                $distribuicao = $this->distribuirLotes($produto['id'], $produto['quantidade']);

                foreach ($distribuicao as $d) {

                    $lote = $d['lote'];
                    $qtd = $d['quantidade'];

                    $subtotal = $qtd * $produto['preco_unitario'];

                    ItemOrcamento::create([
                        'orcamento_id' => $orcamento->id,
                        'produto_id' => $produto['id'],
                        'lote_id' => $lote->id,
                        'quantidade' => $qtd,
                        'preco_unitario' => $produto['preco_unitario'],
                        'subtotal' => $subtotal
                    ]);

                    $lote->quantidade_reservada += $qtd;
                    $lote->save();

                    $total += $subtotal;
                }
                // 🔥 FIM ALTERAÇÃO
            }

            $orcamento->update(['total' => $total]);

            DB::commit();

            return redirect()
                ->route('orcamentos.index')
                ->with('success', 'Orçamento atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    // 🔽 RESTANTE DO CONTROLLER PERMANECE 100% IGUAL (sem alterações)

    /** REATIVAR ORÇAMENTO */
        public function reativar($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status !== Orcamento::STATUS_EXPIRADO) {
            throw new \Exception("Este orçamento não está expirado.");
        }

        $orcamento->status = Orcamento::STATUS_AGUARDANDO_APROVACAO;

        // 🔥 Atualiza a validade (ex: +7 dias)
        $orcamento->validade = now()->addDays(7);

        $orcamento->observacoes = 'Orçamento reativado em: ' . now();

        $orcamento->save();

        return back()->with('success', 'Orçamento reativado com nova validade!');
    }

    /** EXIBE DETALHES */
    public function show($id)
    {
        $orcamento = Orcamento::with([
            'cliente',
            'itens.produto.unidadeMedida',
            'itens.lote'
        ])->findOrFail($id);

        return view('orcamentos.show', compact('orcamento'));
    }

    
    /** FORMULÁRIO DE EDIÇÃO */
   
    // public function edit($id)
    // {
       
    //     $orcamento = Orcamento::with([
    //         'itens.produto.unidadeMedida',
    //         'itens.lote',
    //         'itens.produto.lotes' // 🔥 necessário para estoque e lote automático
    //     ])->findOrFail($id);

    //     if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
    //         $usuario = $orcamento->usuarioEditando;
    //         $nomeUsuario = $usuario->name ?? 'Outro usuário';
    //         return back()->with('error', "Este orçamento está sendo editado por: {$nomeUsuario}");
    //     }

    //     $orcamento->editando_por = auth()->id();
    //     $orcamento->editando_em = now();
    //     $orcamento->save();

    //     $clientes = Cliente::where('ativo', 1)->orderBy('nome')->get();

    //     // 🔥 PRODUTOS COM REGRA DE VALIDADE DINÂMICA
    //     $produtos = Produto::with([
    //             'unidadeMedida',
    //             'lotes' => function ($query) {
    //                 $query->where('status', 1)
    //                     ->where('quantidade_disponivel', '>', 0)
    //                     ->where(function ($q) {
    //                         $q->whereDate('validade_lote', '>=', now())
    //                             ->orWhereNull('validade_lote'); // 🔥 sem validade
    //                     })
    //                     ->orderBy('validade_lote');
    //             }
    //         ])
    //         ->whereHas('lotes', function ($query) {
    //             $query->where('status', 1)
    //                 ->where('quantidade_disponivel', '>', 0)
    //                 ->where(function ($q) {
    //                     $q->whereDate('validade_lote', '>=', now())
    //                         ->orWhereNull('validade_lote'); // 🔥 sem validade
    //                 });
    //         })
    //         ->where('ativo', 1)
    //         ->orderBy('nome')
    //         ->get();

    //     // 🔥 MAPA DE LOTES PARA JS (ESSENCIAL PRA SUA VIEW)
    //     $lotes = [];

    //     foreach ($produtos as $produto) {
    //         $lotes[$produto->id] = $produto->lotes->map(function ($lote) {
    //             return [
    //                 'id' => $lote->id,
    //                 'numero_lote' => $lote->numero_lote,
    //                 'quantidade_disponivel' => $lote->quantidade_disponivel,
    //                 'validade_lote' => $lote->validade_lote
    //             ];
    //         })->values();
    //     }

    //     return view('orcamentos.edit', compact(
    //         'orcamento',
    //         'clientes',
    //         'produtos',
    //         'lotes' // 🔥 AGORA SUA VIEW FUNCIONA 100%
    //     ));
    // }

    public function edit($id)
    {
        $orcamento = Orcamento::with([
            'itens.produto.unidadeMedida',
            'itens.lote',
            'itens.produto.lotes'
        ])->findOrFail($id);

        if ($orcamento->editando_por && $orcamento->editando_por != auth()->id()) {
            $usuario = $orcamento->usuarioEditando;
            $nomeUsuario = $usuario->name ?? 'Outro usuário';
            return back()->with('error', "Este orçamento está sendo editado por: {$nomeUsuario}");
        }

        $orcamento->editando_por = auth()->id();
        $orcamento->editando_em = now();
        $orcamento->save();

        $clientes = Cliente::where('ativo', 1)->orderBy('nome')->get();

        // 🔥 PEGAR PRODUTOS JÁ USADOS NO ORÇAMENTO
        $produtosIdsOrcamento = $orcamento->itens->pluck('produto_id');

        // 🔥 QUERY CORRETA
        $produtos = Produto::with([
                'unidadeMedida',
                'lotes' => function ($query) {
                    $query->where('status', 1)
                        ->where('quantidade_disponivel', '>', 0)
                        ->where(function ($q) {
                            $q->whereDate('validade_lote', '>=', now())
                            ->orWhereNull('validade_lote');
                        })
                        ->orderBy('validade_lote');
                }
            ])
            ->where('ativo', 1)
            ->where(function ($query) use ($produtosIdsOrcamento) {

                // 🔥 REGRA NORMAL
                $query->where(function ($q) {
                    $q->where('controla_validade', 0)
                    ->orWhereHas('lotes', function ($qq) {
                        $qq->where('status', 1)
                            ->where('quantidade_disponivel', '>', 0)
                            ->where(function ($qqq) {
                                $qqq->whereDate('validade_lote', '>=', now())
                                    ->orWhereNull('validade_lote');
                            });
                    });
                })

                // 🔥 GARANTE PRODUTO DO ORÇAMENTO
                ->orWhereIn('id', $produtosIdsOrcamento);
            })
            ->orderBy('nome')
            ->get();

        // 🔥 MAPA DE LOTES (AGORA COM CONTROLA VALIDADE)
        $lotes = [];

        foreach ($produtos as $produto) {
                $produtos = Produto::with([
                'unidadeMedida',
                'lotes'
            ])
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();
        }

        return view('orcamentos.edit', compact(
            'orcamento',
            'clientes',
            'produtos',
            'lotes'
        ));
    }
    /** EXCLUSÃO */
    public function destroy($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status === 'Aprovado') {
            return back()->with('error', 'Não é possível excluir orçamentos aprovados.');
        }

        $orcamento->delete();
        return redirect()->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso.');
    }

     //Aprovar com validade de estoque baixa e entrega qdo estoque ok
     public function aprovar($id)
    {
        $orcamento = Orcamento::with('itens')->findOrFail($id);

        // 🔒 Fora da transação
        if ($orcamento->status === 'Aprovado') {
            return back()->with('warning', 'Orçamento já aprovado.');
        }

        DB::beginTransaction();

        try {

            // 🔒 evita duplicidade de pedido
            if (\App\Models\ItemPedido::where('pedido_id', $orcamento->id)->exists()) {
                throw new \Exception('Pedido já foi gerado para este orçamento.');
            }

            foreach ($orcamento->itens as $item) {

                // 🔒 lock no lote (evita concorrência)
                $lote = Lote::where('id', $item->lote_id)
                    ->lockForUpdate()
                    ->first();

                if (!$lote) {
                    throw new \Exception("Lote não encontrado.");
                }

                // 📦 estoque disponível real
                $estoqueLivre = (float) $lote->quantidade_disponivel - (float) $lote->quantidade_reservada;

                // 📊 quanto dá pra atender agora
                $quantidadeSolicitada = (float) $item->quantidade;
                $atenderAgora = max(0, min($quantidadeSolicitada, $estoqueLivre));

                // 📉 pendente
                $pendente = $quantidadeSolicitada - $atenderAgora;

                // 🔄 reserva estoque somente o que foi atendido
                if ($atenderAgora > 0) {
                    $lote->quantidade_reservada += $atenderAgora;
                    $lote->save();
                }

                // 📅 previsão (exemplo: +7 dias se tiver pendente)
                $previsaoEntrega = $pendente > 0 ? now()->addDays(7) : null;

                // 📌 status do item
                $statusItem = $pendente > 0 ? 'indisponivel' : 'disponivel';

                \App\Models\ItemPedido::create([
                    'pedido_id' => $orcamento->id,
                    'empresa_id' => auth()->user()->empresa_id ?? 1, // pega a empresa do usuário ou usa id 1
                    'produto_id' => $item->produto_id,
                    'lote_id' => $item->lote_id,
                    'quantidade' => $item->quantidade,
                    'quantidade_entregue' => $atenderAgora,
                    'quantidade_pendente' => $pendente,
                    'preco_unitario' => $item->preco_unitario,
                    'subtotal' => $item->subtotal,
                    'status' => $pendente > 0 ? 'indisponivel' : 'disponivel',
                    'previsao_entrega' => $pendente > 0 ? now()->addDays(15) : null,
                ]);
            }

            // 🔄 atualiza orçamento
            $orcamento->refresh();

            $temPendencia = $orcamento->itens
                ->sum(fn($item) => (float) ($item->quantidade_pendente ?? 0)) > 0;

            $orcamento->status = $temPendencia
                ? 'Aguardando Estoque'
                : 'Aprovado';

            $orcamento->save();

            DB::commit();

            return back()->with('success', 'Orçamento aprovado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
       
    }

    /** CANCELAR */
    public function cancelar($id)
    {
      
        $orcamento = Orcamento::findOrFail($id);

        if ($orcamento->status === 'Aprovado') {
            return back()->with('error', 'Não é possível cancelar um orçamento aprovado.');
        }

        $orcamento->status = 'Cancelado';
        $orcamento->save();

        return back()->with('success', 'Orçamento cancelado com sucesso!');
    }

    /** GERA PDF */
    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load(
            'cliente',
            'empresa',
            'itens.produto.unidadeMedida',
            'itens.lote'
        );

        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));

        return $pdf->stream("Orcamento_{$orcamento->id}.pdf");
    }

    public function enviarWhatsApp($id)
    {
        $orcamento = Orcamento::findOrFail($id);

        $pdf = \PDF::loadView('orcamentos.pdf', compact('orcamento'));
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $pdfPath = storage_path("app/public/orcamento/{$fileName}");
        $pdf->save($pdfPath);

        $linkPdf = asset("storage/orcamento/{$fileName}");
        $mensagem = urlencode("Olá! Segue o seu orçamento: {$linkPdf}");
        $telefone = preg_replace('/\D/', '', $orcamento->cliente->telefone ?? '');

        if (!$telefone) {
            return back()->with('error', 'O cliente não possui número cadastrado.');
        }

        return redirect()->away("https://wa.me/55{$telefone}?text={$mensagem}");
    }

    public function visualizarOrcamento($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $fileName = "orcamento_{$orcamento->codigo_orcamento}.pdf";
        $linkPdf = asset("storage/orcamento/{$fileName}");

        return view('orcamentos.visualizar', compact('linkPdf', 'orcamento'));
    }

    public function limparEdicao($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->editando_por = null;
        $orcamento->editando_em = null;
        $orcamento->save();

        return response()->json(['status' => 'ok']);
    }
}