<?php

namespace App\Http\Controllers;
use App\Models\Venda;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Lote;
use App\Models\ItemVenda;
use App\Models\Devolucao;
use App\Models\DevolucaoLog;
use App\Models\PedidoCompra;
use App\Models\Romaneio;
use App\Models\RomaneioOcorrencia;
use App\Services\DevolucaoTriagemService;
use App\Services\Expedicao\RomaneioService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DevolucaoController extends Controller
{
     /**
     * Inicia o tratamento da devolução pela ocorrência logística.
     */
    public function iniciarPorOcorrencia(RomaneioOcorrencia $ocorrencia): RedirectResponse 
    {
        $devolucao = DB::transaction(function () use ($ocorrencia) {
            $ocorrencia = RomaneioOcorrencia::query()
                ->with('devolucao')
                ->lockForUpdate()
                ->findOrFail($ocorrencia->id);

            // Impede duplicidade
            if ($ocorrencia->devolucao) {
                return $ocorrencia->devolucao;
            }

            $entregaItemId = $ocorrencia->entrega_item_id;

            if (! $entregaItemId && $ocorrencia->romaneio_item_id) {
                $entregaItemId = DB::table('romaneio_itens')
                    ->where('id', $ocorrencia->romaneio_item_id)
                    ->value('entrega_item_id');
            }

            $vendaItemId = $entregaItemId
                ? DB::table('entrega_itens')
                    ->where('id', $entregaItemId)
                    ->value('venda_item_id')
                : null;

            if (! $vendaItemId) {
                throw ValidationException::withMessages([
                    'devolucao' =>
                        'Não foi possível identificar o item da venda. '
                        .'Verifique o vínculo entre a entrega e o item da venda.',
                ]);
            }

            $itemVenda = ItemVenda::query()
                ->with('venda')
                ->lockForUpdate()
                ->find($vendaItemId);

            if (! $itemVenda || ! $itemVenda->venda) {
                throw ValidationException::withMessages([
                    'devolucao' =>
                        'O item da venda vinculado à ocorrência não foi encontrado.',
                ]);
            }

            if (! $itemVenda->venda->cliente_id) {
                throw ValidationException::withMessages([
                    'devolucao' =>
                        'A venda vinculada não possui cliente informado.',
                ]);
            }

            $destinoEstoque = match ($ocorrencia->destino_estoque) {
                'Quarentena'   => 'quarentena',
                'Reintegracao' => 'reintegracao',
                'Perda'        => 'baixa_perda',
                'Reposicao'    => 'reposicao_cliente',
                'Tratamento_individual' =>
                    'tratamento_individual',
                default        => 'sem_movimentacao',
            };

            $possuiReposicaoNaTriagem = DB::table(
                'romaneio_ocorrencia_avaliacoes'
            )
                ->where(
                    'romaneio_ocorrencia_id',
                    $ocorrencia->id
                )
                ->where(
                    'destino_sugerido',
                    'Reposicao'
                )
                ->exists();

            $tipo = (
                $ocorrencia->destino_estoque === 'Reposicao'
                || $possuiReposicaoNaTriagem
            )
                ? 'reposicao'
                : 'devolucao';

            $devolucao = Devolucao::create([
                'romaneio_ocorrencia_id' => $ocorrencia->id,
                'romaneio_id'            => $ocorrencia->romaneio_id,
                'entrega_id'             => $ocorrencia->entrega_id,
                'romaneio_item_id'       => $ocorrencia->romaneio_item_id,
                'entrega_item_id'        => $entregaItemId,

                'cliente_id'             => $itemVenda->venda->cliente_id,
                'venda_id'               => $itemVenda->venda_id,
                'venda_item_id'          => $itemVenda->id,
                'produto_id'             => $itemVenda->produto_id,

                'orcamento_origem_id'     => $itemVenda->venda->orcamento_id,
                'orcamento_reposicao_id' => $ocorrencia->orcamento_reposicao_id,

                'quantidade' => $ocorrencia->quantidade_envolvida
                    ?: $itemVenda->quantidade,

                'motivo' => $ocorrencia->descricao
                    ?: 'Devolução originada pela ocorrência logística #'
                        .$ocorrencia->id.'.',

                'tipo'                    => $tipo,
                'status'                  => 'pendente',
                'destino_estoque'         => $destinoEstoque,
                'observacao'              => $ocorrencia->decisao,
                'criado_por'              => auth()->id(),
                'responsavel_analise_id'  =>
                    $ocorrencia->responsavel_analise_id,
                'empresa_id'              => auth()->user()?->empresa_id,
            ]);

            DevolucaoLog::create([
                'devolucao_id' => $devolucao->id,
                'acao'         => 'registrada_por_ocorrencia',
                'descricao'    =>
                    'Tratamento iniciado pela ocorrência logística #'
                    .$ocorrencia->id.'.',
                'usuario'      => auth()->user()->name ?? 'Sistema',
            ]);

            return $devolucao;
        });

        app(DevolucaoTriagemService::class)
            ->sincronizarAvaliacoes($devolucao);

        return redirect()
            ->route('devolucoes.pendentes')
            ->with(
                'success',
                'Devolução #'.$devolucao->id
                    .' criada. Continue o tratamento nesta tela.'
            );
    }

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
   
    // função refinada com padrao das normas ACID, bloqueios para garantir a integridade dos dados mesmo em casos de cliques simultâneos

    public function aprovar(Devolucao $devolucao): RedirectResponse
    {
        $possuiLinhasDaTriagem = $devolucao
            ->lotes()
            ->whereNotNull(
                'romaneio_ocorrencia_avaliacao_id'
            )
            ->exists();

        if ($possuiLinhasDaTriagem) {
            return $this->aprovarDevolucaoDaTriagem(
                $devolucao
            );
        }

        try {
            DB::transaction(function () use ($devolucao) {
                $usuarioId = auth()->id();

                if (! $usuarioId) {
                    throw new \RuntimeException(
                        'Não foi possível identificar o usuário responsável pela aprovação.'
                    );
                }

                $devolucaoLock = Devolucao::query()
                    ->lockForUpdate()
                    ->findOrFail($devolucao->id);

                if ($devolucaoLock->status !== 'pendente') {
                    throw new \RuntimeException(
                        'Esta devolução já foi processada por outro usuário.'
                    );
                }

                if ($devolucaoLock->romaneio_ocorrencia_id) {
                    $valeCompra = DB::table('vale_compras')
                        ->where(
                            'devolucao_id',
                            $devolucaoLock->id
                        )
                        ->lockForUpdate()
                        ->first();

                    if (! $valeCompra) {
                        throw ValidationException::withMessages([
                            'vale_troca' =>
                                'Gere e imprima o voucher de troca antes de aprovar a devolução.',
                        ]);
                    }

                    if ($valeCompra->status !== 'ativo') {
                        throw ValidationException::withMessages([
                            'vale_troca' =>
                                'O voucher vinculado à devolução não está ativo.',
                        ]);
                    }

                    if ((float) $valeCompra->valor <= 0) {
                        throw ValidationException::withMessages([
                            'vale_troca' =>
                                'O voucher vinculado à devolução possui valor inválido.',
                        ]);
                    }
                }

                $itemVenda = ItemVenda::query()
                    ->lockForUpdate()
                    ->find($devolucaoLock->venda_item_id);

                if (! $itemVenda) {
                    throw new \RuntimeException('Item da venda não encontrado.');
                }

                $quantidadeSolicitada = (float) $devolucaoLock->quantidade;
                $quantidadeVendida = (float) $itemVenda->quantidade;

                $jaDevolvido = (float) Devolucao::query()
                    ->where('venda_item_id', $itemVenda->id)
                    ->where('id', '<>', $devolucaoLock->id)
                    ->whereIn('status', [
                        'aprovada',
                        'aprovada_troca',
                        'aprovada_devolucao',
                        'concluida',
                    ])
                    ->sum('quantidade');

                $saldoParaDevolver = max(0, $quantidadeVendida - $jaDevolvido);

                if ($quantidadeSolicitada <= 0) {
                    throw new \RuntimeException(
                        'A quantidade da devolução deve ser maior que zero.'
                    );
                }

                if ($quantidadeSolicitada > $saldoParaDevolver) {
                    throw new \RuntimeException(
                        'Quantidade solicitada excede o saldo disponível para devolução.'
                    );
                }

                $lote = null;
                $loteTecnico = false;

                if ($itemVenda->lote_id) {
                    $lote = Lote::query()
                        ->where('id', $itemVenda->lote_id)
                        ->where('produto_id', $itemVenda->produto_id)
                        ->lockForUpdate()
                        ->first();
                }

                if (! $lote) {
                    $loteTecnico = true;
                    $numeroLoteTecnico = 'DEV-'
                        .$devolucaoLock->id
                        .'-P'
                        .$itemVenda->produto_id;

                    $lote = Lote::query()
                        ->where('numero_lote', $numeroLoteTecnico)
                        ->lockForUpdate()
                        ->first();

                    if (! $lote) {
                        $lote = Lote::create([
                            'numero_lote'          => $numeroLoteTecnico,
                            'produto_id'           => $itemVenda->produto_id,
                            'quantidade'           => 0,
                            'quantidade_reservada' => 0,
                            'quantidade_disponivel'=> 0,
                            'preco_compra'         => 0,
                            'data_compra'          => now()->toDateString(),
                            'lancado_por'          => $usuarioId,
                            'status'               => 1,
                        ]);
                    }
                }

                DB::table('devolucao_lotes')->insert([
                    'devolucao_id'  => $devolucaoLock->id,
                    'produto_id'    => $itemVenda->produto_id,
                    'lote_id'       => $lote->id,
                    'quantidade'    => $quantidadeSolicitada,
                    'venda_id'      => $itemVenda->venda_id,
                    'item_venda_id' => $itemVenda->id,
                    'devolvido_por' => $usuarioId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                if ($devolucaoLock->destino_estoque === 'reintegracao') {
                    if ($loteTecnico) {
                        $lote->quantidade = (float) $lote->quantidade
                            + $quantidadeSolicitada;
                    }

                    $lote->quantidade_disponivel =
                        (float) $lote->quantidade_disponivel
                        + $quantidadeSolicitada;
                    $lote->status = 1;
                    $lote->save();

                    DB::table('produtos')
                        ->where('id', $itemVenda->produto_id)
                        ->increment(
                            'quantidade_estoque',
                            $quantidadeSolicitada,
                            ['updated_at' => now()]
                        );
                }

                if ($devolucaoLock->romaneio_item_id) {
                    $totalAprovadoRomaneioItem = (float) Devolucao::query()
                        ->where('romaneio_item_id', $devolucaoLock->romaneio_item_id)
                        ->where('id', '<>', $devolucaoLock->id)
                        ->whereIn('status', [
                            'aprovada',
                            'aprovada_troca',
                            'aprovada_devolucao',
                            'concluida',
                        ])
                        ->sum('quantidade') + $quantidadeSolicitada;

                    $romaneioItem = DB::table('romaneio_itens')
                        ->where('id', $devolucaoLock->romaneio_item_id)
                        ->lockForUpdate()
                        ->first();

                    if ($romaneioItem) {
                        $novaQuantidadeDevolvida =
                            max(
                                (float) $romaneioItem->quantidade_devolvida,
                                $totalAprovadoRomaneioItem
                            );

                        $novoStatusRomaneioItem =
                            $novaQuantidadeDevolvida >= (float) $romaneioItem->quantidade_prevista
                                ? 'Devolvido'
                                : 'Entregue_parcial';

                        DB::table('romaneio_itens')
                            ->where('id', $romaneioItem->id)
                            ->update([
                                'quantidade_devolvida' => $novaQuantidadeDevolvida,
                                'status'               => $novoStatusRomaneioItem,
                                'retorno_conferido_por'=> $usuarioId,
                                'retorno_conferido_em' => now(),
                                'updated_at'           => now(),
                            ]);
                    }
                }

                if ($devolucaoLock->entrega_item_id) {
                    $totalAprovadoEntregaItem = (float) Devolucao::query()
                        ->where('entrega_item_id', $devolucaoLock->entrega_item_id)
                        ->where('id', '<>', $devolucaoLock->id)
                        ->whereIn('status', [
                            'aprovada',
                            'aprovada_troca',
                            'aprovada_devolucao',
                            'concluida',
                        ])
                        ->sum('quantidade') + $quantidadeSolicitada;

                    $entregaItem = DB::table('entrega_itens')
                        ->where('id', $devolucaoLock->entrega_item_id)
                        ->lockForUpdate()
                        ->first();

                    if ($entregaItem) {
                        $novaQuantidadeDevolvida =
                            max(
                                (float) $entregaItem->quantidade_devolvida,
                                $totalAprovadoEntregaItem
                            );

                        $novoStatusEntregaItem =
                            $novaQuantidadeDevolvida >= (float) $entregaItem->quantidade_prevista
                                ? 'Devolvido'
                                : 'Entregue_parcial';

                        DB::table('entrega_itens')
                            ->where('id', $entregaItem->id)
                            ->update([
                                'quantidade_devolvida' => $novaQuantidadeDevolvida,
                                'status'               => $novoStatusEntregaItem,
                                'updated_at'           => now(),
                            ]);
                    }
                }

                $devolucaoLock->update([
                    'status'        => 'aprovada',
                    'criado_por'    => $usuarioId,
                    'concluida_por' => $usuarioId,
                    'concluida_em'  => now(),
                ]);

                DevolucaoLog::create([
                    'devolucao_id' => $devolucaoLock->id,
                    'acao'         => 'aprovada',
                    'descricao'    => 'Devolução aprovada. Quantidade: '
                        .number_format($quantidadeSolicitada, 3, ',', '.')
                        .'. Lote: '
                        .$lote->numero_lote
                        .'.',
                    'usuario'      => auth()->user()->name ?? 'Sistema',
                ]);

                if ($devolucaoLock->romaneio_ocorrencia_id) {
                    $ocorrencia = RomaneioOcorrencia::query()
                        ->lockForUpdate()
                        ->find($devolucaoLock->romaneio_ocorrencia_id);

                    if ($ocorrencia && ! $ocorrencia->estaResolvida()) {
                        $statusAnterior = $ocorrencia->status;

                        $ocorrencia->update([
                            'status'     => 'Aprovada_devolucao',
                            'updated_at' => now(),
                        ]);

                        DB::table('romaneio_ocorrencia_historicos')->insert([
                            'romaneio_ocorrencia_id' => $ocorrencia->id,
                            'status_anterior'        => $statusAnterior,
                            'status_novo'            => 'Aprovada_devolucao',
                            'evento'                 => 'Devolução aprovada',
                            'descricao'              => 'Devolução #'
                                .$devolucaoLock->id
                                .' aprovada. Quantidade: '
                                .number_format($quantidadeSolicitada, 3, ',', '.')
                                .'. Lote: '
                                .$lote->numero_lote
                                .'.',
                            'registrado_por'         => $usuarioId,
                            'registrado_em'          => now(),
                            'created_at'             => now(),
                            'updated_at'             => now(),
                        ]);
                    }
                }

                $this->consolidarResultadoDaEntrega(
                    $devolucaoLock
                );
            });

            return back()->with(
                'success',
                'Devolução aprovada e fluxo logístico atualizado com sucesso.'
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Erro ao aprovar devolução: '.$e->getMessage()
            );
        }
    }

    private function aprovarDevolucaoDaTriagem(Devolucao $devolucao): RedirectResponse 
    {
        try {
            $resultado = DB::transaction(
                function () use ($devolucao) {
                    $usuarioId = auth()->id();

                    if (! $usuarioId) {
                        throw new \RuntimeException(
                            'Não foi possível identificar o usuário responsável pela aprovação.'
                        );
                    }

                    $devolucao = Devolucao::query()
                        ->lockForUpdate()
                        ->findOrFail($devolucao->id);

                    if (
                        $devolucao->estaConcluida()
                        || $devolucao->estaRejeitada()
                        || $devolucao->estaCancelada()
                    ) {
                        throw ValidationException::withMessages([
                            'devolucao' =>
                                'Esta devolução já está encerrada.',
                        ]);
                    }

                    if (! $devolucao->estaPendente()) {
                        throw ValidationException::withMessages([
                            'devolucao' =>
                                'O estado atual da devolução não permite o processamento.',
                        ]);
                    }

                    $valeCompra = DB::table('vale_compras')
                        ->where(
                            'devolucao_id',
                            $devolucao->id
                        )
                        ->lockForUpdate()
                        ->first();

                    if (! $valeCompra) {
                        throw ValidationException::withMessages([
                            'vale_troca' =>
                                'Gere e imprima o voucher de troca antes de processar as linhas da triagem.',
                        ]);
                    }

                    if ($valeCompra->status !== 'ativo') {
                        throw ValidationException::withMessages([
                            'vale_troca' =>
                                'O voucher vinculado à devolução não está ativo.',
                        ]);
                    }

                    if ((float) $valeCompra->valor <= 0) {
                        throw ValidationException::withMessages([
                            'vale_troca' =>
                                'O voucher vinculado à devolução possui valor inválido.',
                        ]);
                    }

                    $devolucao = app(
                        DevolucaoTriagemService::class
                    )->processar($devolucao);

                    $quantidadePendente = $devolucao
                        ->lotes()
                        ->where(
                            'status_processamento',
                            'Pendente'
                        )
                        ->count();

                    if ($quantidadePendente > 0) {
                        DevolucaoLog::create([
                            'devolucao_id' => $devolucao->id,
                            'acao' =>
                                'tratamento_parcial',
                            'descricao' =>
                                'As linhas sem dependências foram processadas. '
                                .$quantidadePendente
                                .' linha(s) continuam aguardando orçamento ou estoque para reposição.',
                            'usuario' =>
                                auth()->user()->name
                                ?? 'Sistema',
                        ]);

                        return [
                            'concluida' => false,
                            'devolucao' => $devolucao,
                            'pendentes' => $quantidadePendente,
                        ];
                    }

                    $statusNovo = in_array(
                        $devolucao->tipo,
                        ['troca', 'reposicao'],
                        true
                    )
                        ? 'aprovada_troca'
                        : 'aprovada_devolucao';

                    $devolucao->update([
                        'status' => $statusNovo,
                        'criado_por' =>
                            $devolucao->criado_por
                            ?: $usuarioId,
                        'concluida_por' => $usuarioId,
                        'concluida_em' => now(),
                    ]);

                    DevolucaoLog::create([
                        'devolucao_id' => $devolucao->id,
                        'acao' => 'aprovada_por_triagem',
                        'descricao' =>
                            'Devolução aprovada com processamento individual das linhas da triagem. '
                            .'Voucher utilizado: '
                            .$valeCompra->codigo
                            .'.',
                        'usuario' =>
                            auth()->user()->name
                            ?? 'Sistema',
                    ]);

                    $ocorrencia = RomaneioOcorrencia::query()
                        ->lockForUpdate()
                        ->find(
                            $devolucao->romaneio_ocorrencia_id
                        );

                    if (
                        $ocorrencia
                        && ! $ocorrencia->estaResolvida()
                        && ! $ocorrencia->estaCancelada()
                    ) {
                        $statusAnterior = $ocorrencia->status;

                        $statusOcorrencia =
                            $statusNovo === 'aprovada_troca'
                                ? 'Aprovada_troca'
                                : 'Aprovada_devolucao';

                        $ocorrencia->update([
                            'status' => $statusOcorrencia,
                            'updated_at' => now(),
                        ]);

                        DB::table(
                            'romaneio_ocorrencia_historicos'
                        )->insert([
                            'romaneio_ocorrencia_id' =>
                                $ocorrencia->id,
                            'status_anterior' =>
                                $statusAnterior,
                            'status_novo' =>
                                $statusOcorrencia,
                            'evento' =>
                                'Tratamento da triagem concluído',
                            'descricao' =>
                                'Devolução #'
                                .$devolucao->id
                                .' processada por linha de avaliação. '
                                .'Voucher: '
                                .$valeCompra->codigo
                                .'.',
                            'registrado_por' =>
                                $usuarioId,
                            'registrado_em' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $this->consolidarResultadoDaEntrega(
                        $devolucao
                    );

                    return [
                        'concluida' => true,
                        'devolucao' =>
                            $devolucao->fresh(),
                        'pendentes' => 0,
                    ];
                }
            );

            if (! $resultado['concluida']) {
                return back()->with(
                    'warning',
                    'Tratamento parcialmente processado. '
                    .$resultado['pendentes']
                    .' linha(s) aguardam o fluxo de reposição.'
                );
            }

            return redirect()
                ->route(
                    'romaneios.ocorrencias.index',
                    $resultado['devolucao']->romaneio_id
                )
                ->with(
                    'success',
                    'Devolução processada por linha e devolvida à tratativa da ocorrência.'
                );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Erro ao processar a devolução: '
                .$e->getMessage()
            );
        }
    }

    private function consolidarResultadoDaEntrega(
        Devolucao $devolucao
    ): void {
        $romaneioId = (int) (
            $devolucao->romaneio_id
            ?? 0
        );

        if ($romaneioId <= 0) {
            return;
        }

        $romaneio = Romaneio::query()
            ->lockForUpdate()
            ->find($romaneioId);

        if (! $romaneio) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    "O romaneio #{$romaneioId} vinculado à devolução não foi localizado.",
            ]);
        }

        app(RomaneioService::class)
            ->atualizarResultadoFinalEntregas(
                $romaneio
            );
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
        // 1) Busca a devolução trazendo o item específico que foi devolvido e seu produto
        // $devolucao = Devolucao::with(['itemVenda.produto', 'usuario'])->findOrFail($id);
        $devolucao = Devolucao::with([
            'itemVenda.produto',
            'itemVenda.lote',
            'criadoPor',
        ])->findOrFail($id);

        // 2) Busca a venda original trazendo os dados do funcionário do caixa e os itens completos
        $venda = Venda::with([
            'funcionario', 
            'itens.produto.unidadeMedida', 
            'itens.lote'
        ])->find($devolucao->venda_id);

        // 3) Tratamento estrito do cliente para evitar falhas de nós nulos (Padrão VENDABALCAO)
        $clienteId = $venda->cliente_id ?? $devolucao->cliente_id;
        $cliente = null;
        
        if ($clienteId) {
            $cliente = Cliente::find($clienteId);
        }

        // 4) Busca os dados cadastrais ativos da empresa para o cabeçalho do cupom
        $empresa = \App\Models\Empresa::where('ativo', 1)->first();

        // 5) Calcula a matemática financeira específica desta linha de devolução
        // (Seguindo a mesma regra de dedução de desconto proporcional que colocamos no aprovar)
        $itemOriginal = $venda ? $venda->itens->where('id', $devolucao->venda_item_id)->first() : null;
        $valorUnitarioPago = 0;
        $valorTotalEstornado = 0;

        if ($itemOriginal) {
            $quantidadeVendida = (float) $itemOriginal->quantidade;
            $descontoTotalDoItem = (float) ($itemOriginal->desconto ?? 0);
            $descontoUnitario = $descontoTotalDoItem > 0 ? ($descontoTotalDoItem / $quantidadeVendida) : 0;
            
            $valorUnitarioPago = (float) $itemOriginal->preco_unitario - $descontoUnitario;
            $valorTotalEstornado = round((float) $devolucao->quantidade * $valorUnitarioPago, 2);
        }

        // 6) Renderiza o PDF passando o escopo completo e limpo de dados
        $pdf = Pdf::loadView('devolucoes.cupom', compact(
            'devolucao', 
            'venda', 
            'cliente', 
            'empresa',
            'valorUnitarioPago',
            'valorTotalEstornado'
        ));

        // Define o papel para o formato contínuo de bobina térmica de 80mm (Ajustado no DomPDF)
        // 226pt equivale a aproximadamente 80mm de largura útil.
        $pdf->setPaper([0, 0, 226, 500], 'portrait'); 

        return $pdf->stream('cupom_devolucao_'.$devolucao->id.'.pdf');
    }

    public function gerarValeTroca(Devolucao $devolucao)
    {
        try {
            $resultado = DB::transaction(function () use ($devolucao) {
                $devolucaoLock = Devolucao::query()
                    ->with([
                        'produto',
                        'itemVenda.produto.unidadeMedida',
                        'itemVenda.lote',
                        'venda.funcionario',
                        'criadoPor',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($devolucao->id);

                if (! $devolucaoLock->romaneio_ocorrencia_id) {
                    throw new \RuntimeException(
                        'Esta emissão é exclusiva para devoluções originadas da tratativa de ocorrências.'
                    );
                }

                if (
                    in_array(
                        $devolucaoLock->status,
                        ['rejeitada', 'cancelada'],
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'Não é possível emitir vale-troca para uma devolução rejeitada ou cancelada.'
                    );
                }

                $itemOriginal = $devolucaoLock->itemVenda;

                if (! $itemOriginal) {
                    throw new \RuntimeException(
                        'O item original da venda não foi encontrado.'
                    );
                }

                $quantidadeVendida = (float) $itemOriginal->quantidade;

                if ($quantidadeVendida <= 0) {
                    throw new \RuntimeException(
                        'A quantidade original da venda é inválida.'
                    );
                }

                $quantidadeDevolvida = (float) $devolucaoLock->quantidade;
                $precoUnitarioOriginal = (float) $itemOriginal->preco_unitario;
                $descontoTotalItem = (float) ($itemOriginal->desconto ?? 0);

                $descontoUnitario = $descontoTotalItem > 0
                    ? $descontoTotalItem / $quantidadeVendida
                    : 0;

                $valorUnitarioPago = round(
                    max(0, $precoUnitarioOriginal - $descontoUnitario),
                    2
                );

                $valorTotalEstornado = round(
                    $quantidadeDevolvida * $valorUnitarioPago,
                    2
                );

                if ($valorTotalEstornado <= 0) {
                    throw new \RuntimeException(
                        'Não foi possível determinar um valor válido para o vale-troca.'
                    );
                }

                $valeCompra = DB::table('vale_compras')
                    ->where('devolucao_id', $devolucaoLock->id)
                    ->lockForUpdate()
                    ->first();

                if (! $valeCompra) {
                    $codigo = 'VT-'
                        .str_pad(
                            (string) $devolucaoLock->id,
                            6,
                            '0',
                            STR_PAD_LEFT
                        )
                        .'-'
                        .strtoupper(
                            \Illuminate\Support\Str::random(8)
                        );

                    $valeCompraId = DB::table('vale_compras')
                        ->insertGetId([
                            'cliente_id'     => $devolucaoLock->cliente_id,
                            'devolucao_id'   => $devolucaoLock->id,
                            'valor'          => $valorTotalEstornado,
                            'valor_utilizado'=> 0,
                            'codigo'         => $codigo,
                            'status'         => 'ativo',
                            'validade_em'    => now()->addDays(7)->toDateString(),
                            'data_utilizacao'=> null,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

                    $valeCompra = DB::table('vale_compras')
                        ->where('id', $valeCompraId)
                        ->first();

                    DevolucaoLog::create([
                        'devolucao_id' => $devolucaoLock->id,
                        'acao'         => 'vale_troca_emitido',
                        'descricao'    => 'Vale-troca '
                            .$codigo
                            .' emitido no valor de R$ '
                            .number_format(
                                $valorTotalEstornado,
                                2,
                                ',',
                                '.'
                            )
                            .'.',
                        'usuario'      => auth()->user()->name ?? 'Sistema',
                    ]);
                }

                return [
                    'devolucao'           => $devolucaoLock,
                    'venda'               => $devolucaoLock->venda,
                    'valeCompra'          => $valeCompra,
                    'valorUnitarioPago'   => $valorUnitarioPago,
                    'valorTotalEstornado' => (float) $valeCompra->valor,
                ];
            });

            $devolucao = $resultado['devolucao'];
            $venda = $resultado['venda'];
            $valeCompra = $resultado['valeCompra'];
            $valorUnitarioPago = $resultado['valorUnitarioPago'];
            $valorTotalEstornado = $resultado['valorTotalEstornado'];

            $cliente = Cliente::find($devolucao->cliente_id);

            $empresa = Empresa::query()
                ->where('ativo', 1)
                ->first();

            $pdf = Pdf::loadView(
                'devolucoes.cupom',
                compact(
                    'devolucao',
                    'venda',
                    'cliente',
                    'empresa',
                    'valeCompra',
                    'valorUnitarioPago',
                    'valorTotalEstornado'
                )
            );

            /*
            * A Blade possui duas vias e não deve ser comprimida
            * no formato térmico de 80 mm.
            */
            $pdf->setPaper('a4', 'portrait');

            return $pdf->stream(
                'vale_troca_'.$valeCompra->codigo.'.pdf'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Não foi possível gerar o vale-troca: '.$e->getMessage()
            );
        }
    }

    /**
     * Exibe as devoluções que ainda precisam de tratamento.
     */
    public function pendentes(Request $request)
    {
        $devolucaoId = $request->integer('devolucao_id');

        $statusPendentes = [
            'pendente',
            'aguardando_evidencias',
            'em_analise',
            'aguardando_decisao',
            'aguardando_orcamento',
            'orcamento_criado',
            'aguardando_estoque',
            'em_reposicao',
        ];

        $devolucoes = Devolucao::query()
            ->with([
                'itemVenda.venda.cliente',
                'itemVenda.produto',
                'itemVenda.lote',
                'produto',
                'venda.cliente',
                'ocorrencia.anexos',
                'lotes.avaliacao',
                'lotes.lote',
                'lotes.movimentacoes',
            ])
            ->addSelect([
                'vale_compra_id' => DB::table('vale_compras')
                    ->select('id')
                    ->whereColumn(
                        'vale_compras.devolucao_id',
                        'devolucoes.id'
                    )
                    ->limit(1),

                'vale_compra_codigo' => DB::table('vale_compras')
                    ->select('codigo')
                    ->whereColumn(
                        'vale_compras.devolucao_id',
                        'devolucoes.id'
                    )
                    ->limit(1),

                'vale_compra_status' => DB::table('vale_compras')
                    ->select('status')
                    ->whereColumn(
                        'vale_compras.devolucao_id',
                        'devolucoes.id'
                    )
                    ->limit(1),
            ])
            ->when(
                $devolucaoId > 0,
                function ($query) use ($devolucaoId) {
                    $query->where('devolucoes.id', $devolucaoId);
                },
                function ($query) use ($statusPendentes) {
                    $query->whereIn(
                        'devolucoes.status',
                        $statusPendentes
                    );
                }
            )
            ->orderByDesc('devolucoes.created_at')
            ->get();

        if (
            $devolucaoId > 0
            && $devolucoes->isEmpty()
        ) {
            return redirect()
                ->route('devolucoes.pendentes')
                ->with(
                    'error',
                    'A devolução informada não foi encontrada.'
                );
        }

        return view(
            'devolucoes.pendentes',
            compact(
                'devolucoes',
                'devolucaoId'
            )
        );
    }
        
}