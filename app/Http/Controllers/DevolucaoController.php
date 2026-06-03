<?php

namespace App\Http\Controllers;
use App\Models\Venda;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Lote;
use App\Models\ItemVenda;
use App\Models\Devolucao;
use App\Models\DevolucaoLog;
use App\Models\PedidoCompra;
use Illuminate\Support\Facades\DB;

class DevolucaoController extends Controller
{
    public function index()
    {
        $itens = collect();
        $vendas = collect(); 
        $clientes = Cliente::orderBy('nome')->get();

        $produtos = Produto::whereIn(
            'id',
            Devolucao::distinct()->pluck('produto_id')
        )->orderBy('nome')->get();

        $lotes = Lote::whereIn(
            'produto_id',
            Devolucao::distinct()->pluck('produto_id')
        )
        ->orderBy('id')
        ->pluck('id');

        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function buscar(Request $request)
    {
        $search = trim($request->input('search'));

        // Variáveis de suporte da index para evitar erros de renderização
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::whereIn('id', Devolucao::distinct()->pluck('produto_id'))->orderBy('nome')->get();
        $lotes = Lote::whereIn('produto_id', Devolucao::distinct()->pluck('produto_id'))->orderBy('id')->pluck('id');

        if (empty($search)) {
            $itens = collect();
            $vendas = collect();
            return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
        }

        // Query principal focada na Item_Vendas
        $vendas_paginadas = DB::table('Item_Vendas as iv')
            ->join('vendas as v', 'v.id', '=', 'iv.venda_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'v.cliente_id')
            ->join('produtos as p', 'p.id', '=', 'iv.produto_id')
            ->select(
                'v.id as venda_id',
                'v.data_venda',
                'v.total as valor_total_venda',
                DB::raw('COALESCE(c.nome, "Cliente Não Vinculado") as cliente_nome'),
                DB::raw('COALESCE(c.cpf_cnpj, "---") as cliente_cpf_cnpj'),
                DB::raw('COALESCE(c.tipo, "---") as cliente_tipo'),
                
                // Dados do Item da Venda
                'iv.id as venda_item_id',
                'p.nome as produto_nome',
                'iv.quantidade as quantidade_comprada', 
                'iv.preco_unitario as preco_unitario', 
                'iv.subtotal as subtotal',
                
                // 🔥 CORREÇÃO CRÍTICA: Subquery direta para buscar o número do lote original usando o lote_id da Item_Vendas
                DB::raw('(SELECT COALESCE(numero_lote, "Nenhum") FROM lotes WHERE lotes.id = iv.lote_id LIMIT 1) as numero_lote'),

                // Histórico de devoluções aprovadas/pendentes deste item específico
                DB::raw('(SELECT COALESCE(SUM(d.quantidade), 0) 
                          FROM devolucoes d 
                          WHERE d.venda_item_id = iv.id AND d.status != "rejeitada") as quantidade_devolvida'),
                          
                DB::raw('(SELECT COALESCE(SUM(d.quantidade * prod.preco_venda), 0) 
                          FROM devolucoes d 
                          JOIN produtos prod ON prod.id = d.produto_id
                          WHERE d.venda_item_id = iv.id AND d.status != "rejeitada") as valor_extornado')
            )
            ->where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('v.id', '=', (int)$search);
                } else {
                    $query->where('c.nome', 'like', "%{$search}%");
                }
            })
            ->orderByDesc('v.id')
            ->paginate(10);

        // Processa os saldos matemáticos reais em memória
        foreach ($vendas_paginadas as $item) {
            $item->quantidade_disponivel = (float)$item->quantidade_comprada - (float)$item->quantidade_devolvida;
            $item->valor_disponivel = (float)$item->quantidade_disponivel * (float)$item->preco_unitario;
            
            $item->valor_total = $item->valor_total_venda;
            $item->qtde_disponivel = $item->quantidade_disponivel;

        }

        $vendas_paginadas->appends(['search' => $search]);

        $vendas = $vendas_paginadas;
        $itens = collect();

        // ... final do seu método buscar atual ...

        // Processa os saldos matemáticos reais em memória
        foreach ($vendas_paginadas as $item) {
            $item->quantidade_disponivel = (float)$item->quantidade_comprada - (float)$item->quantidade_devolvida;
            $item->valor_disponivel = (float)$item->quantidade_disponivel * (float)$item->preco_unitario;
            
            $item->valor_total = $item->valor_total_venda;
            $item->qtde_disponivel = $item->quantidade_disponivel;

            // 🔥 NOVO: Carrega todos os itens e produtos desta venda específica para alimentar o modal do cupom
           $item->venda_completa = Venda::with([
                'cliente',
                'itens.produto.unidadeMedida',
                'itens.lote',
                'funcionario'
            ])->find($item->venda_id);

            $pagamentosDaVenda = DB::table('pagamentos_venda')
                ->where('venda_id', $item->venda_id)
                ->get();

            $item->venda_completa->setRelation('pagamentos', $pagamentosDaVenda);

            $item->empresa = Empresa::where('ativo', 1)->first();

            $item->terminalId = DB::table('caixas')
                ->where('id', $item->venda_completa->caixa_id)
                ->value('terminal_id') ?? 0;

            $pagamentoDinheiro = $pagamentosDaVenda
                ->where('forma_pagamento', 'dinheiro')
                ->first();

            $item->troco = $pagamentoDinheiro
                ? (float) $pagamentoDinheiro->troco
                : 0;

            $item->pagoEmDinheiro = $pagamentoDinheiro
                ? ((float)$pagamentoDinheiro->valor + (float)$item->troco)
                : 0;
        }

        $vendas_paginadas->appends(['search' => $search]);
        // ... restante do método igual ...


        return view('devolucoes.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function registrar($venda_id)
    {
        $venda = Venda::with(['itens.produto', 'itens.lote', 'itens.devolucoes'])
            ->where('id', $venda_id)
            ->firstOrFail();

        $temPendente = $venda->itens->some(function ($item) {
            return $item->devolucoes->contains('status', 'Pendente');
        });

        if ($temPendente) {
            $msg = '
                <div class="alert alert-danger d-flex justify-content-between align-items-center mb-0 p-3" style="font-size: 15px; border-radius: 8px;">
                    <div>
                        <strong>Atenção!</strong><br>
                        Já existe uma devolução pendente para esta venda.<br>
                        Finalize a devolução pendente antes de abrir uma nova.
                    </div>
                    <a href="/devolucoes/pendentes" class="btn btn-sm btn-primary fw-bold shadow-sm ms-3" style="white-space: nowrap;">
                        <i class="bi bi-clock-history"></i> Ver pendentes
                    </a>
                </div>
            ';

            return redirect()->back()->with('error', $msg);
        }

        // 🔥 Query estruturada adaptando as colunas validadas do MariaDB à sua lógica original
            $itensVenda = DB::table('Item_Vendas as iv')
                ->join('produtos as p', 'p.id', '=', 'iv.produto_id')
                ->leftJoin('lotes as l', 'l.id', '=', 'iv.lote_id')
                ->select([
                    'iv.id as item_venda_id',
                    'iv.id',
                    'iv.venda_id',
                    'iv.produto_id',
                    'p.nome as produto_nome',
                    'iv.preco_unitario as preco_unitario_item',
                    'iv.subtotal as valor_compra',
                    
                    // 🔥 SOLUÇÃO: Seleciona como quantidade_comprada E cria o apelido qtd_comprada para aceitar os dois padrões
                    'iv.quantidade as quantidade_comprada',
                    'iv.quantidade as qtd_comprada', 
                    
                    DB::raw('COALESCE(l.numero_lote, "Nenhum") as numero_lote'),
                    
                    DB::raw('(SELECT COALESCE(SUM(d.quantidade), 0) 
                            FROM devolucoes d 
                            WHERE d.venda_item_id = iv.id 
                            AND d.status != "rejeitada") as quantidade_devolvida'),
                            
                    DB::raw('(SELECT MAX(d.created_at) 
                            FROM devolucoes d 
                            WHERE d.venda_item_id = iv.id 
                            AND d.status != "rejeitada") as data_ultima_devolucao')
                ])
                ->where('iv.venda_id', $venda_id)
                ->get();


        // 3. Processa o SALDO DISPONÍVEL na memória para cada linha (Sua matemática intacta)
        foreach ($itensVenda as $item) {
            $item->quantidade_disponivel = (float)$item->quantidade_comprada - (float)$item->quantidade_devolvida;
        }

        // Seu return original repassando a coleção completa de dados calculados para a View
        return view('devolucoes.registrar', compact('venda', 'itensVenda'));
    }
   
    public function salvar(Request $request)
    {
        //  dd($request->all());
        // 🔥 CORREÇÃO: Captura o ID do item na primeira linha para usá-lo na validação abaixo
        $itemId = $request->input('item_id');

        $request->validate([
            'item_id' => 'required|exists:Item_Vendas,id',
            'quantidade' => 'nullable|numeric|min:1',
            'completo' => 'nullable|boolean',
            'motivo' => 'required|string|max:255',
            'motivo_outro' => 'nullable|string|max:255',
            // Agora o Laravel encontra a variável $itemId perfeitamente aqui:
            "imagem1_{$itemId}" => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            "imagem2_{$itemId}" => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            "imagem3_{$itemId}" => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            "imagem4_{$itemId}" => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $motivoSelecionado = $request->motivo;
        $motivoFinal = ($motivoSelecionado === 'Outro motivo' || $motivoSelecionado === 'Outro')
            ? ($request->motivo_outro ?? $motivoSelecionado)
            : $motivoSelecionado;

        $existingPending = Devolucao::where('venda_item_id', $itemId)
            ->where('status', 'pendente')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'Já existe uma devolução pendente para este item. Aguarde a análise antes de registrar outra.');
        }

        DB::beginTransaction();
        try {
            $existingPending = Devolucao::where('venda_item_id', $itemId)
                ->where('status', 'pendente')
                ->lockForUpdate()
                ->exists();

            if ($existingPending) {
                DB::rollBack();
                return back()->with('error', 'Já existe uma devolução pendente para este item (verificação final).');
            }

            // Buscamos os dados da venda com o ID isolado
            $itemVendaDados = DB::table('Item_Vendas as iv')
                ->join('vendas as v', 'v.id', '=', 'iv.venda_id')
                ->select('iv.*', 'v.cliente_id', 'v.id as venda_id')
                ->where('iv.id', $itemId)
                ->first();

            if (!$itemVendaDados) {
                DB::rollBack();
                return back()->with('error', 'Item da venda não encontrado.');
            }

            // Cálculo do histórico atualizado
            $quantidadeJaDevolvida = DB::table('devolucoes')
                ->where('venda_item_id', $itemId)
                ->where('status', '!=', 'rejeitada')
                ->sum('quantidade');

            $qtdeDisponivel = (float)$itemVendaDados->quantidade - (float)$quantidadeJaDevolvida;

            $quantidadeDevolver = ($request->has('completo') && $request->completo) 
                ? $qtdeDisponivel 
                : ((float)($request->quantidade ?? 0));

            if ($quantidadeDevolver > $qtdeDisponivel || $quantidadeDevolver <= 0) {
                DB::rollBack();
                return back()->with('error', 'Quantidade informada inválida ou excede o limite permitido.');
            }

            // Upload de Imagens dinâmico buscando o padrão "imagemX_ID"
            $imagens = [];
            $itemId = $request->item_id;

            for ($i = 1; $i <= 4; $i++) {
                $campo = 'imagem' . $i;
                
                // Como os arquivos vêm limpos (imagem1, imagem2...), o Laravel encontra direto:
                if ($request->hasFile($campo)) {
                    $file = $request->file($campo);
                    
                    // Gera o nome único do arquivo mantendo seu padrão original
                    $nomeArquivo = 'vendaItem_' . $itemId . '_foto' . $i . '_' . time() . '.' . $file->getClientOriginalExtension();
                    
                    // Move diretamente para public/imgDevolucoes/
                    $file->move(public_path('imgDevolucoes'), $nomeArquivo);
                    
                    $imagens[$campo] = $nomeArquivo;
                } else {
                    $imagens[$campo] = null;
                }
            }

            // 3. O Model recebe o array exatamente com as chaves corretas:
            $devolucao = Devolucao::create([
                'cliente_id'    => $itemVendaDados->cliente_id,
                'venda_id'      => $itemVendaDados->venda_id,
                'venda_item_id' => $itemVendaDados->id,
                'produto_id'    => $itemVendaDados->produto_id,
                'quantidade'    => $quantidadeDevolver,
                'motivo'        => $motivoFinal,
                'status'        => 'pendente',
                'imagem1'       => $imagens['imagem1'],
                'imagem2'       => $imagens['imagem2'],
                'imagem3'       => $imagens['imagem3'],
                'imagem4'       => $imagens['imagem4'],
            ]);

        
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao'         => 'registrada',
                'descricao'    => 'Devolução registrada pelo cliente. Aguardando aprovação.',
                'usuario'      => auth()->user()->name ?? 'Sistema',
            ]);

            DB::commit();

            return redirect()->route('devolucoes.pendentes')
                ->with('success', 'Devolução registrada com sucesso e aguardando aprovação.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao registrar devolução: ' . $e->getMessage());
        }
    }

    // funcionando bem, mas sem bloqueios, o que pode causar problemas em casos de cliques simultâneos
    // public function aprovar(Devolucao $devolucao)
    // {

    //     try {
    //         DB::beginTransaction();

    //         $devolucao->refresh();

    //         // =========================================================
    //         // 1) Buscar item da venda
    //         // =========================================================
    //         $itemVenda = ItemVenda::find($devolucao->venda_item_id);

    //         if (!$itemVenda) {
    //             DB::rollBack(); // 🔥 CORREÇÃO: Cancela a transação antes de voltar
    //             return back()->with('error', 'Item da venda não encontrado.');
    //         }

    //         // =========================================================
    //         // 2) Validar quantidade disponível para devolução
    //         // =========================================================
    //         $quantidadeSolicitada = $devolucao->quantidade;
    //         $quantidadeVendida = $itemVenda->quantidade;

    //         $jaDevolvido = Devolucao::where('venda_item_id', $itemVenda->id)
    //             ->where('status', 'aprovada')
    //             ->sum('quantidade');

    //         $saldoParaDevolver = $quantidadeVendida - $jaDevolvido;

    //         if ($quantidadeSolicitada > $saldoParaDevolver) {
    //             DB::rollBack(); // 🔥 CORREÇÃO: Cancela a transação antes de voltar
    //             return back()->with('error', 'Quantidade solicitada excede o permitido.');
    //         }

    //         // =========================================================
    //         // 3) Buscar LOTE REAL usado na venda
    //         // =========================================================
    //         $lote = Lote::find($itemVenda->lote_id);

    //         if (!$lote) {
    //             DB::rollBack(); // 🔥 CORREÇÃO: Cancela a transação antes de voltar
    //             return back()->with('error', 'Lote da venda não encontrado.');
    //         }

    //         // =========================================================
    //         // 4) Registrar devolução no pivot devolucao_lotes
    //         // =========================================================
    //         DB::table('devolucao_lotes')->insert([
    //             'devolucao_id'  => $devolucao->id,
    //             'produto_id'    => $itemVenda->produto_id,
    //             'lote_id'       => $lote->id,
    //             'quantidade'    => $quantidadeSolicitada,
    //             'venda_id'      => $itemVenda->venda_id,
    //             'item_venda_id' => $itemVenda->id,
    //             'devolvido_por' => auth()->id(),
    //             'created_at'    => now(),
    //             'updated_at'    => now(),
    //         ]);

    //         // =========================================================
    //         // 5) Repor estoque EXATAMENTE no lote da venda
    //         // =========================================================
    //         $lote->quantidade_disponivel += $quantidadeSolicitada;
    //         $lote->save();

    //        // =========================================================
    //         // 6) Atualizar devolução principal (Query Direta Forçada no Banco)
    //         // =========================================================
    //         DB::table('devolucoes')
    //             ->where('id', $devolucao->id)
    //             ->update([
    //                 'status' => 'aprovada',
    //                 'criado_por' => auth()->id(),
    //                 'updated_at' => now()
    //             ]);

    //         // =========================================================
    //         // 7) Log opcional
    //         // =========================================================
    //         DevolucaoLog::create([
    //             'devolucao_id' => $devolucao->id,
    //             'acao'         => 'aprovada',
    //             'descricao'    => "Devolução aprovada. Quantidade: {$quantidadeSolicitada}.",
    //             'usuario'      => auth()->user()->name ?? 'Sistema',
    //         ]);

    //         DB::commit(); // ✅ Salva todas as alterações fisicamente no banco de dados

    //         return back()->with('success', 'Devolução aprovada com sucesso!');

    //     } catch (\Exception $e) {
    //         DB::rollBack(); // ⚠️ Em caso de falha mecânica, cancela tudo
    //         return back()->with('error', 'Erro ao aprovar devolução: '.$e->getMessage());
    //     }
    // }

    // função refinada com padrao das normas ACID, bloqueios para garantir a integridade dos dados mesmo em casos de cliques simultâneos
    public function aprovar(Devolucao $devolucao)
    {
        try {
            DB::beginTransaction();

            // 🔥 CORREÇÃO ACID (Isolamento): Dá um lock de escrita na devolução atual
            // Ninguém consegue reprocessar esta linha até acabar este commit
            $devolucaoLock = Devolucao::where('id', $devolucao->id)->lockForUpdate()->first();

            // Validação crucial de estado: Se já foi aprovada ou rejeitada por outro clique, cancela
            if ($devolucaoLock->status !== 'pendente') {
                DB::rollBack();
                return back()->with('error', 'Esta devolução já foi processada por outro usuário.');
            }

            // 1) Buscar item da venda aplicando LOCK de leitura/escrita
            $itemVenda = ItemVenda::where('id', $devolucao->venda_item_id)->lockForUpdate()->first();

            if (!$itemVenda) {
                DB::rollBack();
                return back()->with('error', 'Item da venda não encontrado.');
            }

            // 2) Validar quantidade disponível para devolução de forma isolada
            $quantidadeSolicitada = $devolucao->quantidade;
            $quantidadeVendida = $itemVenda->quantidade;

            $jaDevolvido = Devolucao::where('venda_item_id', $itemVenda->id)
                ->where('status', 'aprovada')
                ->sum('quantidade');

            $saldoParaDevolver = $quantidadeVendida - $jaDevolvido;

            if ($quantidadeSolicitada > $saldoParaDevolver) {
                DB::rollBack();
                return back()->with('error', 'Quantidade solicitada excede o permitido.');
            }

            // 3) Buscar LOTE REAL usado na venda aplicando LOCK (Impede cliques simultâneos de corromperem a soma)
            $lote = Lote::where('id', $itemVenda->lote_id)->lockForUpdate()->first();

            if (!$lote) {
                DB::rollBack();
                return back()->with('error', 'Lote da venda não encontrado.');
            }

            // 4) Registrar devolução no pivot devolucao_lotes
            DB::table('devolucao_lotes')->insert([
                'devolucao_id'  => $devolucao->id,
                'produto_id'    => $itemVenda->produto_id,
                'lote_id'       => $lote->id,
                'quantidade'    => $quantidadeSolicitada,
                'venda_id'      => $itemVenda->venda_id,
                'item_venda_id' => $itemVenda->id,
                'devolvido_por' => auth()->id(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // 5) Repor estoque EXATAMENTE no lote da venda (Agora seguro contra concorrência)
            $lote->quantidade_disponivel += $quantidadeSolicitada;
            $lote->save();

            // 6) Atualizar devolução principal (Query Direta Forçada no Banco)
            DB::table('devolucoes')
                ->where('id', $devolucao->id)
                ->update([
                    'status' => 'aprovada',
                    'criado_por' => auth()->id(),
                    'updated_at' => now()
                ]);

            // 7) Log opcional
            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao'         => 'aprovada',
                'descricao'    => "Devolução aprovada. Quantidade: {$quantidadeSolicitada}.",
                'usuario'      => auth()->user()->name ?? 'Sistema',
            ]);

            DB::commit();
            return back()->with('success', 'Devolução aprovada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar devolução: '.$e->getMessage());
        }
    }

    public function rejeitar(Devolucao $devolucao)
    {
        DB::transaction(function () use ($devolucao) {
            $devolucao->status = 'rejeitada';
            $devolucao->save();

            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao' => 'rejeitada',
                'descricao' => 'Devolução rejeitada pelo setor responsável.',
                'usuario' => auth()->user()->name ?? 'Administrador',
            ]);
        });

        return redirect()->route('devolucoes.index')->with('success', 'Devolução rejeitada com sucesso!');
    }

    public function pendentes()
    {
        $devolucoes = Devolucao::with([
            'itemVenda.venda.cliente',
            'itemVenda.produto',
            'itemVenda.lote',
        ])->where('status', 'pendente')->get();

        return view('devolucoes.pendentes', compact('devolucoes'));
    }

    private function carregarDadosCupom($vendaId)
    {
        $venda = Venda::with([
            'cliente',
            'itens.produto.unidadeMedida',
            'itens.lote',
            'funcionario'
        ])->findOrFail($vendaId);

        $pagamentosDaVenda = DB::table('pagamentos_venda')
            ->where('venda_id', $vendaId)
            ->get();

        $empresa = Empresa::where('ativo', 1)->first();

        $descontoTotal = $venda->itens->sum('desconto');

        $terminalId = DB::table('caixas')
            ->where('id', $venda->caixa_id)
            ->value('terminal_id') ?? 0;

        $pagamentoDinheiro = $pagamentosDaVenda
            ->where('forma_pagamento', 'dinheiro')
            ->first();

        $troco = $pagamentoDinheiro
            ? (float) $pagamentoDinheiro->troco
            : 0;

        $valorLiquidoDinheiro = $pagamentoDinheiro
            ? (float) $pagamentoDinheiro->valor
            : 0;

        $pagoEmDinheiro = $valorLiquidoDinheiro + $troco;

        $venda->setRelation('pagamentos', $pagamentosDaVenda);

        return compact(
            'venda',
            'empresa',
            'descontoTotal',
            'pagoEmDinheiro',
            'troco',
            'terminalId'
        );
    }
    public function gerarCupom($id)
    {
        $devolucao = Devolucao::findOrFail($id);
        $venda = Venda::with('itens.produto')->find($devolucao->venda_id);
        $cliente = Cliente::find($venda->cliente_id ?? $devolucao->cliente_id);
        $itens = $venda ? $venda->itens : [];

        $pdf = Pdf::loadView('devolucoes.cupom', compact('devolucao', 'venda', 'cliente', 'itens'));
        return $pdf->stream('cupom_devolucao_'.$devolucao->id.'.pdf');
    }
    
}
