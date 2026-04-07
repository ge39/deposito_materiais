<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Models\Empresa;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\User;
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
   /**
 * 🔹 Distribui a quantidade de um produto entre vários lotes
 * Retorna um array com: ['lote' => Lote, 'quantidade' => float]
 */
    private function distribuirLotes(int $produtoId, float $quantidadeDesejada): array
    {
        $resultado = [];
        $restante = $quantidadeDesejada;

        // Busca todos os lotes do produto, ativos ou parciais
        $lotes = Lote::where('produto_id', $produtoId)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($restante <= 0) break;

            $disponivel = max(0, $lote->quantidade_disponivel - $lote->quantidade_reservada);

            $atendida = min($disponivel, $restante);
            $pendente  = $restante - $atendida;

            // Atualiza quantidade_reservada do lote
            $lote->quantidade_reservada += $atendida;
            $lote->save();

            $resultado[] = [
                'lote' => $lote,
                'quantidade' => $quantidadeDesejada, // total do item solicitado
                'atendida' => $atendida,
                'pendente' => $pendente,
            ];

            $restante -= $atendida;
        }

        // Se ainda restar quantidade depois de todos os lotes, pega o último lote
        if ($restante > 0 && $lotes->isNotEmpty()) {
            $ultimoLote = $lotes->last();
            $resultado[] = [
                'lote' => $ultimoLote,
                'quantidade' => $restante,
                'atendida' => 0,
                'pendente' => $restante,
            ];
        }

        return $resultado;
    }

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
                (!$lote->validade_lote || $lote->validade_lote >= now());
        })->values();

        // Soma de todos os lotes válidos do produto
        $estoqueDisponivel = $lotesValidos->sum(fn($lote) => $lote->quantidade_disponivel);

        $lotes[$produto->id] = [
            'controla_validade' => $produto->controla_validade,

            'todos' => $produto->lotes->map(function ($lote) {
                return [
                    'id' => $lote->id,
                    'numero_lote' => $lote->numero_lote,
                    'quantidade_disponivel' => $lote->quantidade_disponivel,
                    'validade_lote' => $lote->validade_lote,
                ];
            })->values(),

            'validos' => $lotesValidos->map(function ($lote) {
                return [
                    'id' => $lote->id,
                    'numero_lote' => $lote->numero_lote,
                    'quantidade_disponivel' => $lote->quantidade_disponivel,
                    'validade_lote' => $lote->validade_lote,
                ];
            })->values(),

            // 🔥 NOVO: estoque total do produto
            'estoque_disponivel' => $estoqueDisponivel,
        ];
        }

        return view('orcamentos.create', compact(
            'clientes',
            'produtos',
            'lotes'
        ));
    }

       /**
     * Criar um novo orçamento
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:1',
            'validade'   => 'required|date',
        ]);

       

        DB::transaction(function () use ($request) {
            $empresa = Empresa::where('ativo', 1)->first();

            if (!$empresa) {
                throw new \Exception('Nenhuma empresa ativa encontrada.');
            }

            // Cria o orçamento
            $orcamento = Orcamento::create([
                'cliente_id'     => $request->cliente_id,
                'empresa_id' => $empresa->id,
                'data_orcamento' => now(),
                'validade'       => $request->validade,
                'codigo_orcamento'=> now()->format('YmdHis'),
                'status'         => Orcamento::STATUS_AGUARDANDO_APROVACAO,
                'observacoes'    => $request->observacoes ?? null,
                'total'          => 0,
                'ativo'          => true,
                'editando_por'       => auth()->id(),
                'editando_em'        => now()->format('YmdHis') ,
            ]);

            $totalOrcamento = 0;
            $orcamento->codigo_orcamento = now()->format('YmdHis') . $orcamento->id;
            $orcamento->save();

            // Percorre os itens enviados
            foreach ($request->produtos as $itemReq) {

                $produtoId = $itemReq['id'];
                $quantidadeDesejada = $itemReq['quantidade'];
                $precoUnitario = $itemReq['preco_unitario'] ?? 0;
                $subtotal = $quantidadeDesejada * $precoUnitario;
                $totalOrcamento += $subtotal;

                // Buscar lotes válidos do produto
                $lotes = Lote::where('produto_id', $produtoId)
                    ->where('status', 1)
                    ->orderBy('created_at')
                    ->lockForUpdate()
                    ->get();

                $quantidadeAtendida = 0;
                $quantidadePendente = $quantidadeDesejada;
                $loteId = null;

                foreach ($lotes as $lote) {
                    if ($quantidadePendente <= 0) break;

                    $disponivel = $lote->quantidade_disponivel - $lote->quantidade_reservada;

                    if ($disponivel <= 0) continue;

                    $qtdParaReservar = min($quantidadePendente, $disponivel);

                    // Atualiza lote
                    $lote->quantidade_reservada += $qtdParaReservar;
                    $lote->save();

                    $quantidadeAtendida += $qtdParaReservar;
                    $quantidadePendente -= $qtdParaReservar;

                    $loteId = $lote->id; // último lote usado
                }

                // Define status do item
                if ($quantidadeAtendida == 0) {
                    $status = 'indisponivel';
                } elseif ($quantidadePendente > 0) {
                    $status = 'parcial';
                } else {
                    $status = 'disponivel';
                }

                // Previsão de entrega: mantém a mesma data inicial ou pode usar o lote mais antigo
                $previsaoEntrega = $lotes->first()?->validade_lote ?? now();

                // Cria item do orçamento
                ItemOrcamento::create([
                    'orcamento_id'      => $orcamento->id,
                    'produto_id'        => $produtoId,
                    'lote_id'           => $loteId,
                    'quantidade'        => $quantidadeDesejada,
                    'quantidade_atendida'=> $quantidadeAtendida,
                    'quantidade_pendente'=> $quantidadePendente,
                    'preco_unitario'    => $precoUnitario,
                    'subtotal'          => $subtotal,
                    'status'            => $status,
                    'previsao_entrega'  => $previsaoEntrega,
                ]);
            }

            // Atualiza total do orçamento
            $orcamento->total = $totalOrcamento;

            // Atualiza status geral do orçamento
            $temPendentes = $orcamento->itens()->whereIn('status', ['indisponivel','parcial'])->count() > 0;
            $orcamento->status = $temPendentes
                ? 'Aguardando Estoque'
                : 'Aguardando Aprovacao';

            $orcamento->save();
        });

        return redirect()->route('orcamentos.index')
            ->with('success', 'Orçamento criado com sucesso.');
    }


   public function update(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);

        DB::transaction(function () use ($request, $orcamento) {

            // 🔹 Devolver estoque dos itens atuais
            foreach ($orcamento->itens as $item) {
                if ($item->lote_id) {
                    $lote = Lote::find($item->lote_id);
                    if ($lote) {
                        $lote->quantidade_reservada -= $item->quantidade_atendida ?? 0;
                        if ($lote->quantidade_reservada < 0) $lote->quantidade_reservada = 0;
                        $lote->save();
                    }
                }
            }

            // 🔹 Deleta itens antigos
            $orcamento->itens()->delete();

            $totalOrcamento = 0;

            // 🔹 Percorre produtos enviados
            foreach ($request->produtos as $produtoReq) {
                $produtoId = $produtoReq['id'];
                $quantidadeDesejada = $produtoReq['quantidade'];
                $precoUnitario = $produtoReq['preco_unitario'] ?? 0;
                $subtotalProduto = 0;

                // Buscar lotes válidos
                $lotes = Lote::where('produto_id', $produtoId)
                    ->where('status', 1)
                    ->orderBy('created_at')
                    ->lockForUpdate()
                    ->get();

                $quantidadeAtendida = 0;
                $quantidadePendente = $quantidadeDesejada;
                $ultimoLoteId = null;

                foreach ($lotes as $lote) {
                    if ($quantidadePendente <= 0) break;

                    $disponivel = $lote->quantidade_disponivel - $lote->quantidade_reservada;
                    if ($disponivel <= 0) continue;

                    $qtdParaReservar = min($quantidadePendente, $disponivel);

                    // Atualiza lote
                    $lote->quantidade_reservada += $qtdParaReservar;
                    $lote->save();

                    $quantidadeAtendida += $qtdParaReservar;
                    $quantidadePendente -= $qtdParaReservar;
                    $ultimoLoteId = $lote->id;

                    $subtotalProduto += $qtdParaReservar * $precoUnitario;
                }

                // Determina status do item
                if ($quantidadeAtendida == 0) {
                    $statusItem = 'indisponivel';
                } elseif ($quantidadePendente > 0) {
                    $statusItem = 'parcial';
                } else {
                    $statusItem = 'disponivel';
                }

                // Previsão de entrega: usa validade do lote mais antigo ou adiciona 7 dias
                $previsaoEntrega = $lotes->first()?->validade_lote ?? now()->addDays(7);
                // Cria item do orçamento
                ItemOrcamento::create([
                    'orcamento_id'       => $orcamento->id,
                    'produto_id'         => $produtoId,
                    'lote_id'            => $ultimoLoteId,
                    'quantidade'         => $quantidadeDesejada,
                    'quantidade_atendida'=> $quantidadeAtendida,
                    'quantidade_pendente'=> $quantidadePendente,
                    'preco_unitario'     => $precoUnitario,
                    'subtotal'           => $subtotalProduto,
                    'status'             => $statusItem,
                    'previsao_entrega'   => now()->addDays(7),
                    'ativo'              => true,
                     'observacoes'        => $request->observacoes ?? 'Sem observações',
                ]);

                $totalOrcamento += $subtotalProduto;
            }

            // 🔹 Atualiza total e status do orçamento
            $temPendentes = $orcamento->itens()->whereIn('status', ['indisponivel','parcial'])->count() > 0;
            $orcamento->update([
                'total' => $totalOrcamento,
                'status'=> $temPendentes ? 'Aguardando Estoque' : 'Aguardando Aprovacao',
            ]);
        });

        return redirect()->route('orcamentos.index')
                        ->with('success', 'Orçamento atualizado com sucesso!');
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