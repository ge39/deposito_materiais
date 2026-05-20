<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditoriaCaixa;
use App\Models\AuditoriaDetalhe;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use App\Models\PagamentoVenda;
use App\Services\CaixaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class FechamentoCaixaController extends Controller
{
    /**
     * Mostra a view de fechamento/auditoria
     */

    // public function index($caixaId)
    // {
    //     $caixa = Caixa::with('movimentacoes')->findOrFail($caixaId);
    
    //     $total_esperado = CaixaService::total_esperado($caixaId);

    //     $total_entradas = DB::table('movimentacoes_caixa')
    //         ->where('caixa_id', $caixaId)
    //         // ->whereIn('tipo', ['abertura', 'entrada_manual', 'venda'])
    //         // ->where('tipo', 'venda')
    //         ->sum('valor');
           

    //     $total_saidas = DB::table('movimentacoes_caixa')
    //     ->where('caixa_id', $caixaId)
    //     ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
    //     ->sum('valor');
        

    //     /** ============================
    //      * INICIALIZAÇÃO SEGURA
    //      * ============================ */
    //     $divergencia = 0;
    //     $totaisPorForma = [];
    //     $totalGeralSistema = 0;
    //     $total_sangrias = 0;

    //      $total_sangrias = DB::table('movimentacoes_caixa')
    //     ->where('caixa_id', $caixaId)
    //     ->where('tipo', 'sangria')
    //     ->where('forma_pagamento', 'sangria')
    //     ->sum('valor');

    //     /** ============================
    //      * TOTAIS DO SISTEMA POR FORMA
    //      * ============================ */
    //     if ($caixa->vendas->count()) {
    //         foreach ($caixa->vendas as $venda) {
    //             foreach ($venda->pagamentos as $pag) {
    //                 if ($pag->status !== 'confirmado') {
    //                     continue;
    //                 }

    //                 $forma = $pag->forma_pagamento;
    //                 $totaisPorForma[$forma] = ($totaisPorForma[$forma] ?? 0) + $pag->valor;
    //             }
    //         }

    //         $totalGeralSistema = array_sum($totaisPorForma);
    //     }

    //         return view('fechamento_caixa.index', compact(
    //             'caixa',
    //             'total_entradas',
    //             'total_saidas',
    //             'total_esperado',
    //             'divergencia',
    //             'totaisPorForma',
    //             'totalGeralSistema',
    //             'total_sangrias'
    //         ));
    // }

    public function index($caixaId)
    {
        // 1️⃣ Carrega o caixa garantindo que ele exista
        $caixa = Caixa::findOrFail($caixaId);

        // 2️⃣ TOTAL ENTRADAS: Soma tudo o que injetou dinheiro (Abertura, Vendas do PDV, Aportes/Entradas Manuais)
        $total_entradas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['abertura', 'venda'])
            ->sum('valor');

        // 3️⃣ TOTAL SAÍDAS: Soma retiradas manuais, despesas e cancelamentos
        $total_saidas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa'])
            ->sum('valor');

        // 4️⃣ SANGRIAS: Isola o total de sangrias para dedução ou exibição limpa
        $total_sangrias = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->where('tipo', 'sangria')
            ->sum('valor');

        // 5️⃣ FORMAS DE PAGAMENTO DO SISTEMA: Inicializa a matriz zerada para a Blade
        $totaisPorForma = [
            'dinheiro'       => 0.00,
            'pix'            => 0.00,
            'carteira'       => 0.00,
            'cartao_debito'  => 0.00,
            'cartao_credito' => 0.00
        ];

        // Busca e agrupa os valores de vendas ou lançamentos manuais já consolidados nesta fita
        $movimentosAgrupados = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['venda', 'entrada_manual']) // Lê as vendas do dia E os fechamentos dos caixas antigos
            ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
            ->groupBy('forma_pagamento')
            ->get();

        foreach ($movimentosAgrupados as $mov) {
            $forma = strtolower(trim($mov->forma_pagamento));
            // Remove possíveis variações de escrita (ex: "cartao debito" vira "cartao_debito")
            $forma = str_replace(' ', '_', $forma); 

            if (array_key_exists($forma, $totaisPorForma)) {
                $totaisPorForma[$forma] = (float) $mov->total;
            }
        }

        // 6️⃣ MATEMÁTICA CONSOLIDADA DO SISTEMA
        $totalGeralSistema = array_sum($totaisPorForma);

        // O esperado em dinheiro físico é: Fundo de Troco + Entradas em Dinheiro Vivo - Saídas/Sangrias
        $dinheiroDoSistema = $totaisPorForma['dinheiro'] ?? 0.00;
        $total_esperado = ($caixa->fundo_troco + $dinheiroDoSistema) - ($total_saidas + $total_sangrias);
        
        $divergencia = 0.00;

        // Carrega o histórico completo de movimentações para alimentar a tabela do rodapé da Blade
        $caixa->setRelation('movimentacoes', $caixa->movimentacoes()->orderBy('id', 'asc')->get());

        return view('fechamento_caixa.index', compact(
            'caixa',
            'total_entradas',
            'total_saidas',
            'total_esperado',
            'divergencia',
            'totaisPorForma',
            'totalGeralSistema',
            'total_sangrias'
        ));
    }

   
    public function listaCaixas()
    {
        $caixas = Caixa::with(['usuario', 'terminal'])
            ->whereIn('status', ['aberto', 'inconsistente'])
            ->orderBy('data_abertura', 'asc')
            ->get();

        return view('fechamento_caixa.listaCaixas', compact('caixas'));
    }

      /**
     * Mostra a view para lançamento manual de valores
     */
    public function lancarValores(Caixa $caixa)
    {
        return view('fechamento_caixa.lancar_valores', compact('caixa'));
    }

    public function possuiVendas()
    {
        return $this->vendas()->exists();
    }

    public function fechar(Request $request, Caixa $caixa)
    {
        // dd('entrou no metodo');
        
        // Aceita tanto o status 'aberto' quanto o 'inconsistente' se o operador precisar re-enviar
        if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') {
            return back()->withErrors('Este caixa já está fechado.');
            // dd('passou do estaAberto');
        }

        // 🌟 A CORREÇÃO DE LOGICA: Verifica se existem linhas do tipo 'venda' gravadas na fita do caixa
        $temMovimentoDeVenda = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixa->id)
            ->where('tipo', 'venda')
            ->exists();

        // Se NÃO existirem vendas registradas na fita, segue o fluxo administrativo sem movimento
        if (!$temMovimentoDeVenda) {
            return $this->fecharSemMovimento($request, $caixa);
        }
        
        // dd('passou do estaAberto');
        
        // Se existirem vendas, segue o fluxo correto de auditoria dos valores da gaveta
        return $this->fecharMovimentoComAuditoria($request, $caixa);
    }


    private function fecharSemMovimento(Request $request, Caixa $caixa)
    {
        $request->validate(['motivo_fechamento' => 'required|string|max:255']);
        $userId = auth()->id();
        $motivo = $request->input('motivo_fechamento');

        return DB::transaction(function () use ($caixa, $userId, $motivo) {
            $caixa->refresh();
            if ($caixa->status !== 'aberto') { 
                throw new \Exception('Caixa já modificado por outro terminal.'); 
            }

            // 1. Gera cabeçalho da auditoria (Troco esperado == Troco recebido)
            $auditoriaId = $this->criarAuditoriaCabecalho(
                $caixa->id, 
                $userId, 
                (float)$caixa->fundo_troco, 
                (float)$caixa->fundo_troco, 
                "Fechamento administrativo sem movimento: {$motivo}"
            );

            // 2. Prepara matriz zerada (Apenas dinheiro herda o troco)
            $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
            $valoresSistemas = array_fill_keys($formas, 0.00);
            $valoresSistemas['dinheiro'] = (float)$caixa->fundo_troco;
            
            // Executa o método privado compartilhado
            $this->salvarAuditoriaDetalhes($auditoriaId, $formas, $valoresSistemas, $valoresSistemas);

            // 3. Grava histórico na fita do caixa aberto
            $this->salvarMovimentacaoHistorica($caixa->id, $auditoriaId, $userId, 'fechamento_sem_movimento', 'fechamento', (float)$caixa->fundo_troco, $motivo);

            // 4. Atualiza o cabeçalho principal
            $caixa->update([
                'valor_fechamento' => (float)$caixa->fundo_troco, 
                'status' => 'fechado_sem_movimento', 
                'data_fechamento' => now(), 
                'fechado_por' => $userId, 
                'observacao' => $motivo
            ]);

            return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
                ->with('success', 'Caixa encerrado administrativamente.');
        });
    }

   public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    {
        // 1️⃣ VALIDAÇÃO INICIAL: Garante a presença dos campos obrigatórios na requisição
        $request->validate([
            'dinheiro'       => 'required', 
            'pix'            => 'required', 
            'carteira'       => 'required',
            'cartao_debito'  => 'required', 
            'cartao_credito' => 'required'
        ]);

        $userId = auth()->id();
        $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
        
        // 2️⃣ TRATAMENTO DE ENTRADAS DO OPERADOR (Evita quebras com vírgulas ou máscaras do front-end)
        $valoresFisicos = [];
        foreach ($formas as $forma) {
            $valorRaw = $request->input($forma, 0);
            // Se o operador digitar "150,50", o PHP converte para o float válido "150.50"
            $valoresFisicos[$forma] = is_numeric($valorRaw) 
                ? (float) $valorRaw 
                : (float) str_replace(',', '.', str_replace('.', '', $valorRaw));
        }

        // 3️⃣ INÍCIO DA TRANSAÇÃO ATÔMICA (ACID)
        return DB::transaction(function () use ($caixa, $userId, $formas, $valoresFisicos) {
            // Recarrega o estado atual do banco para evitar concorrência ou cliques duplos
            $caixa->refresh();
            
            if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') { 
                throw new \Exception('Este caixa já foi processado ou fechado por outra sessão.'); 
            }

            // 4️⃣ BUSCA E AGRUPAMENTO SEGURO DOS DADOS DE FATURAMENTO DO PDV
            $vendasDoCaixaRaw = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->where('tipo', 'venda')
                ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
                ->groupBy('forma_pagamento')
                ->get();

            // Normaliza as chaves removendo espaços invisíveis que quebram o ->get()
            $vendasDoCaixa = collect();
            foreach ($vendasDoCaixaRaw as $venda) {
                $formaLimpa = str_replace(' ', '_', strtolower(trim($venda->forma_pagamento)));
                $vendasDoCaixa->put($formaLimpa, $venda);
            }

            // 5️⃣ APURAÇÃO DA MATEMÁTICA DO SISTEMA POR MEIO DE PAGAMENTO
            $valoresSistemas = [];
            $totalSistemaGeral = 0.00;
            $totalFisicoGeral = array_sum($valoresFisicos);

            foreach ($formas as $forma) {
                // 🌟 BLINDAGEM CRÍTICA: Verifica se a forma existe na coleção antes de acessar a propriedade ->total
                $registroVenda = $vendasDoCaixa->get($forma);
                $vendaForma = $registroVenda ? (float)$registroVenda->total : 0.00;
                
                // O dinheiro projetado herda as vendas em espécie + o fundo de troco físico inicial
                $valoresSistemas[$forma] = ($forma === 'dinheiro') 
                    ? ($vendaForma + (float)$caixa->fundo_troco) 
                    : $vendaForma;
                
                // Acumula no somatório geral esperado
                $totalSistemaGeral += $valoresSistemas[$forma];
            }

            $diferencaGeral = $totalFisicoGeral - $totalSistemaGeral;

            /*

            |--------------------------------------------------------------------------
            | 6️⃣ EXECUÇÃO OBRIGATÓRIA DA PERSISTÊNCIA FINANCEIRA
            |--------------------------------------------------------------------------
            */
            // 1. Grava a folha de rosto na tabela auditorias_caixa
            $auditoriaId = $this->criarAuditoriaCabecalho(
                $caixa->id, 
                $userId, 
                $totalSistemaGeral, 
                $totalFisicoGeral, 
                'Fechamento comercial auditado e validado'
            );
            
            // 2. 🌟 INVOCAÇÃO SEGURA: Alimenta a tabela auditoria_detalhes com os status 'correto' ou 'divergente'
            $this->salvarAuditoriaDetalhes($auditoriaId, $valoresSistemas, $valoresFisicos);

            // 3. Registra as linhas históricas individuais de contagem na fita movimentacoes_caixa (Padrão Caixa 296)
            foreach ($valoresFisicos as $forma => $valor) {
                $this->salvarMovimentacaoHistorica(
                    $caixa->id, 
                    $auditoriaId, 
                    $userId, 
                    'entrada_manual', 
                    $forma, 
                    (float)$valor, 
                    "Valor físico informado no fechamento"
                );
            }

            // 4. Atualiza o status definitivo do caixa de acordo com a margem de quebra tolerada (1 centavo)
            $novoStatusCaixa = (abs($diferencaGeral) <= 0.01) ? 'fechado' : 'inconsistente';
            
            $caixa->update([
                'valor_fechamento' => $totalFisicoGeral, 
                'status'           => $novoStatusCaixa, 
                'data_fechamento'  => now(), 
                'fechado_por'      => $userId
            ]);

            // Redireciona de forma segura para a sua view de confirmação mapeada
            return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
                ->with('success', 'Conferência física processada e registrada com sucesso.');
        });
    }

    private function criarAuditoriaCabecalho(int $caixaId, int $userId, float $sistema, float $fisico, string $obs): int
    {
        $diferenca = $fisico - $sistema;
        return DB::table('auditorias_caixa')->insertGetId([
            'caixa_id'         => $caixaId, 
            'user_id'          => $userId, 
            'codigo_auditoria' => 'AUD-' . $caixaId . '-' . now()->format('YmdHis'),
            'total_sistema'    => $sistema, 
            'total_fisico'     => $fisico, 
            'diferenca'        => $diferenca,
            'status'           => (abs($diferenca) <= 0.01 ? 'concluida' : 'inconsistente'), // ENUM auditorias_caixa
            'observacao'       => $obs, 
            'data_auditoria'   => now()
        ]);
    }

    private function salvarAuditoriaDetalhes(int $auditoriaId, array $valoresSistemas, array $valoresFisicos): void
    {
        // Lista estática explícita para garantir o pente-fino das 5 formas da sua Blade
        $listaFormas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];

        foreach ($listaFormas as $forma) {
            $sistema = (float)($valoresSistemas[$forma] ?? 0.00);
            $fisico  = (float)($valoresFisicos[$forma] ?? 0.00);
            $diff    = $fisico - $sistema;

            DB::table('auditoria_detalhes')->insert([
                'auditoria_id'    => $auditoriaId,
                'forma_pagamento' => $forma,
                'total_sistema'   => $sistema,
                'total_fisico'    => $fisico,
                'diferenca'       => $diff,
                'status'          => (abs($diff) <= 0.01 ? 'correto' : 'divergente'), // ENUM da sua tabela auditoria_detalhes
                'created_at'      => now(),
                'updated_at'      => now()
            ]);
        }
    }


    private function salvarMovimentacaoHistorica(int $caixaId, int $auditoriaId, int $userId, string $tipo, string $forma, float $valor, string $obs): void
    {
        DB::table('movimentacoes_caixa')->insert([
            'caixa_id'          => $caixaId, 
            'auditoria_id'      => $auditoriaId, 
            'user_id'           => $userId, 
            'tipo'              => $tipo,
            'forma_pagamento'   => $forma, 
            'valor'             => $valor, 
            'valor_auditado'    => 0.00, 
            'bandeira'          => null,
            'origem_id'         => null, 
            'observacao'        => $obs, 
            'data_movimentacao' => now(), 
            'created_at'        => now(), 
            'updated_at'        => now()
        ]);
    }

    /**
     * Lança valores manuais no caixa (entradas e bandeiras)
     */
    public function lancar_valores(Request $request, $caixaId)
    {
        $caixa = Caixa::findOrFail($caixaId);

        // Desbloqueia temporariamente se estiver bloqueado
        if ($caixa->status === 'bloqueado') {
            $caixa->status = 'aberto';
            $caixa->save();
        }

        $userId = auth()->id();

        // Lista de valores por forma de pagamento
        $valores = [
            'dinheiro'        => 'Dinheiro',
            'pix'             => 'Pix',
            'carteira'        => 'Carteira',
            'cartao_debito'   => 'Cartão Débito',
            'cartao_credito'  => 'Cartão Crédito',
        ];

        DB::transaction(function() use ($request, $caixa, $valores, $userId) {
            // Entradas manuais
            foreach ($valores as $campo => $descricao) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id'  => $userId,
                        'tipo'     => 'entrada_manual',
                        'valor'    => $valor,
                        'observacao' => "Lançamento manual: {$descricao}",
                        'data_movimentacao' => now(),
                    ]);
                }
            }

            // Bandeiras de cartão (informativo)
            $bandeiras = [
                'bandeira_visa'       => 'Visa',
                'bandeira_mastercard' => 'Mastercard',
                'bandeira_elo'        => 'Elo',
                'bandeira_amex'       => 'Amex',
                'bandeira_hipercard'  => 'Hipercard',
            ];

            foreach ($bandeiras as $campo => $nome) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id'  => $userId,
                        'tipo'     => 'entrada_manual',
                        'valor'    => $valor,
                        'observacao' => "Lançamento manual: Bandeira {$nome}",
                        'data_movimentacao' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('fechamento.auditar', $caixa->id)
            ->with('success', 'Movimentações lançadas com sucesso!');
    }

    private function registrarSaidas($request, $caixa, $auditoria = null): void
    {
        $map = [
            'saida_sangria' => 'sangria',
            'saida_despesa' => 'despesa',
            'saida_ajuste'  => 'ajuste_negativo',
            'saida_outros'  => 'outras_saidas',
        ];

        foreach ($map as $campo => $tipo) {
            $valor = (float) $request->$campo;

            if ($valor > 0) {
                MovimentacaoCaixa::create([
                    'caixa_id'          => $caixa->id,
                    'auditoria_id'      => $auditoria->id ?? null,
                    'tipo'              => 'saida_manual',
                    'forma_pagamento'   => $tipo,
                    'valor'             => $valor,
                    'observacao'        => ucfirst(str_replace('_', ' ', $tipo)),
                    'user_id'           => auth()->id(),
                    'data_movimentacao' => now(),
                ]);
            }
        }
    }
   public function ajustarDivergencias(Request $request, int $caixaId)
    {
        $request->validate([
            'formas'   => 'required|array|min:1',
            'formas.*' => 'required',
        ]);
        
        DB::transaction(function () use ($request, $caixaId) {

            // 1️⃣ CÁLCULO DAS VENDAS REAIS DO SISTEMA DIRETO DA FITA OFICIAL
            $totalVendas = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixaId)
                ->where('tipo', 'venda')
                ->sum('valor');

            $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

            if ($caixa->status !== 'inconsistente' && $caixa->status !== 'aberto') {
                throw new \Exception('Este caixa não está disponível para auditoria ou correção.');
            }

            $userId = auth()->id();
            $totalAuditado = 0.00; // Acumulador do fechamento físico real

            // 2️⃣ BUSCA O CABEÇALHO DA AUDITORIA GERAL PARA CONEXÃO DE DETALHES
            $auditoria = DB::table('auditorias_caixa')
                ->where('caixa_id', $caixaId)
                ->latest('data_auditoria')
                ->first();

            foreach ($request->formas as $forma => $valorStr) {
                // Conversão segura usando o helper do seu sistema
                $valorAuditado = $this->parseValorBR($valorStr);
                $totalAuditado += $valorAuditado; // 👈 CORRIGIDO: Acumula o valor físico real para fechar o caixa

                // Captura o valor real do sistema direto da fita de movimentações
                $valorSistema = DB::table('movimentacoes_caixa')
                    ->where('caixa_id', $caixaId)
                    ->where('tipo', 'venda')
                    ->where('forma_pagamento', $forma)
                    ->sum('valor');

                // Se for dinheiro vivo, adiciona o fundo de troco inicial ao esperado do sistema
                if ($forma === 'dinheiro') {
                    $valorSistema += (float)$caixa->fundo_troco;
                }

                // 3️⃣ ATUALIZAÇÃO DA TABELA HISTÓRICA DO CAIXA
                MovimentacaoCaixa::updateOrCreate(
                    [
                        'caixa_id'        => $caixa->id,
                        'tipo'            => 'auditoria',
                        'forma_pagamento' => $forma,
                    ],
                    [
                        'user_id'         => $userId,
                        'valor'           => $valorSistema,     
                        'valor_auditado'  => $valorAuditado,    
                        'observacao'      => 'Auditoria por forma de pagamento realizada com sucesso',
                        'data_movimentacao'=> now(),
                    ]
                );

                // 4️⃣ SINCRO COM A TABELA auditoria_detalhes QUE ATIVAMOS
                if ($auditoria) {
                    $novaDiferencaForma = $valorAuditado - $valorSistema;
                    
                    DB::table('auditoria_detalhes')
                        ->where('auditoria_id', $auditoria->id)
                        ->where('forma_pagamento', $forma)
                        ->update([
                            'total_fisico' => $valorAuditado, // ou valor_informado conforme seu banco
                            'diferenca'    => $novaDiferencaForma,
                            'status'       => abs($novaDiferencaForma) <= 0.01 ? 'correto' : 'divergente',
                            'updated_at'   => now()
                        ]);
                }

                // 5️⃣ GERAÇÃO DE AJUSTES CONTÁBEIS SE HOUVER DIFERENÇA
                $diferenca = $valorAuditado - $valorSistema;

                if (abs($diferenca) > 0.01) {
                    DB::table('movimentacoes_caixa')->insert([
                        'caixa_id'          => $caixa->id,
                        'auditoria_id'      => $auditoria ? $auditoria->id : null,
                        'user_id'           => $userId,
                        'tipo'              => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
                        'forma_pagamento'   => $forma,
                        'valor'             => abs($diferenca),
                        'valor_auditado'    => 0.00,
                        'bandeira'          => null,
                        'origem_id'         => null,
                        'observacao'        => "[AJUSTE AUDITORIA FISCAL] Forma: " . ucfirst($forma),
                        'data_movimentacao' => now(),
                        'created_at'        => now(),
                        'updated_at'        => now()
                    ]);
                }
            }

            // 6️⃣ ATUALIZAÇÃO DO MODEL DE CONFERÊNCIA ADMINISTRATIVA
            MovimentacaoCaixa::where('caixa_id', $caixa->id)
                ->where('tipo', 'fechamento')
                ->latest('id')
                ->update([
                    'valor_auditado' => $totalVendas,               
                    'observacao'     => 'Auditoria concluída e homologada por forma de pagamento',
                    'user_id'        => $userId,
                    'updated_at'     => now(),
                ]);

            // 7️⃣ ATUALIZAÇÃO DEFINITIVA DO CABEÇALHO DO CAIXA (Preenche a data e o valor da imagem!)
            $caixa->update([
                'valor_fechamento' => $totalAuditado, // 👈 CORRIGIDO: Injeta o valor real no card da imagem
                'status'           => 'fechado',       // Força o status definitivo para Fechado
                'data_fechamento'  => now(),          // 👈 CORRIGIDO: Registra o relógio e remove o traço (-) da imagem
                'fechado_por'      => $userId,
            ]);

            // Atualiza a tabela auditorias_caixa se ela existir
            if ($auditoria) {
                $novaDiferencaGeral = $totalAuditado - (float)$auditoria->total_sistema;
                DB::table('auditorias_caixa')
                    ->where('id', $auditoria->id)
                    ->update([
                        'total_fisico' => $totalAuditado,
                        'diferenca'    => $novaDiferencaGeral,
                        'status'       => abs($novaDiferencaGeral) <= 0.01 ? 'concluida' : 'corrigida',
                    ]);
            }
        });

        // 8️⃣ REDIRECIONAMENTO SEGURO POR LINK LIMPO DIRETO
        // Envia o alerta com a chave 'auditoria_sucesso' idêntica à que a sua View espera receber
        return redirect("/fechamento_caixa/confirmacao/{$caixaId}")
            ->with('auditoria_sucesso', 'A divergência do Caixa #' . $caixaId . ' foi corrigida pela auditoria.');
    }

   public function divergencias($caixaId)
    {
        // 1️⃣ Busca o caixa atual do depósito com as movimentações
        $caixa = Caixa::with(['usuario', 'movimentacoes'])->findOrFail($caixaId);

        // 2️⃣ Busca o cabeçalho de auditoria do fechamento
        $auditoria = DB::table('auditorias_caixa')
            ->where('caixa_id', $caixaId)
            ->latest('data_auditoria')
            ->first();

        // Inicialização estrita das matrizes em minúsculo com underline para a sua Blade
        $totaisPorForma = [
            'dinheiro'       => 0.00,
            'pix'            => 0.00,
            'carteira'       => 0.00,
            'cartao_debito'  => 0.00,
            'cartao_credito' => 0.00
        ];
        $totaisInformados = $totaisPorForma;

        // 3️⃣ CAPTURA DAS COLUNAS DA TABELA auditoria_detalhes (Suporta total_ ou valor_)
        if ($auditoria) {
            $detalhes = DB::table('auditoria_detalhes')
                ->where('auditoria_id', $auditoria->id)
                ->get();

            foreach ($detalhes as $det) {
                $forma = str_replace(' ', '_', strtolower(trim($det->forma_pagamento)));

                if (array_key_exists($forma, $totaisPorForma)) {
                    // 🌟 CORREÇÃO CIRÚRGICA: Tenta ler 'total_sistema' ou 'valor_sistema' para não zerar o array
                    $totaisPorForma[$forma] = (float) ($det->total_sistema ?? $det->valor_sistema ?? 0.00);
                    
                    // Tenta ler 'total_fisico' ou 'valor_informado' para preencher a gaveta
                    $totaisInformados[$forma] = (float) ($det->total_fisico ?? $det->valor_informado ?? $det->valor_fisico ?? 0.00);
                }
            }
        }

        // 4️⃣ VARIÁVEIS MACRO DO BLOCO SUPERIOR AZUL
        $totalGeralSistema = $auditoria ? (float)$auditoria->total_sistema : 0.00;
        $total_entradas    = $auditoria ? (float)$auditoria->total_fisico : 0.00;
        
        $total_saidas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'sangria'])
            ->sum('valor');

        return view('fechamento_caixa.corrigir_divergencias', compact(
            'caixa',
            'totaisPorForma',
            'totaisInformados',
            'totalGeralSistema',
            'total_entradas',
            'total_saidas'
        ));
    }


    private function parseValorBR($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.00;
        }

        // Remove tudo que não seja número, ponto, vírgula ou sinal
        $valor = trim($valor);
        $valor = preg_replace('/[^\d.,-]/', '', $valor);

        // Trata negativo apenas no início
        $negativo = false;
        if (str_starts_with($valor, '-')) {
            $negativo = true;
            $valor = substr($valor, 1);
        }

        // Se houver vírgula, assume padrão BR
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        $numero = round((float) $valor, 2);

        return $negativo ? -$numero : $numero;
    }

   // chama o modal Fechamento pelo caixa F10
   public function fechamento($caixaId)
    {
        $caixa = Caixa::with('vendas.pagamentos')
            ->where('id', $caixaId)
            ->where('status', 'aberto')
            ->firstOrFail();

        return view('fechamento_caixa.fechamento', compact('caixa'));
    }

     //rota padrao para mostrar a confirmação do fechamento, seja com ou sem movimento
    public function confirmacao(Caixa $caixa)
    {
        if ($caixa->status === 'inconsistente') {
            return view('fechamento_caixa.confirmacao_inconsistente', compact('caixa'));
        }

        return view('fechamento_caixa.confirmacao', compact('caixa'));
    }

    //FAZ A AUDITORIA DO FECHAMENTO
    public function auditar(Request $request, $id)
    {
        $movimentacao = MovimentacaoCaixa::findOrFail($id);

        if ($movimentacao->tipo !== 'fechamento') {
            return redirect()->back()
                ->withErrors('Somente o fechamento de caixa pode ser auditado.');
        }

        $request->validate([
            'valor_auditado' => ['required', 'numeric', 'min:0'],
        ]);

        $movimentacao->update([
            'valor_auditado' => $request->valor_auditado,
            'user_id' => auth()->id(),
            'observacao' => 'Correção de divergência',
        ]);

        return back()->with('success', 'Auditoria registrada com sucesso.');
    }
   
    //CORRIGE AS DIVERGÊNCIAS APÓS A AUDITORIA FISCAL e imprime mensagem na tela
   public function auditoria($caixa)
    {
        $caixa = Caixa::findOrFail($caixa);

        $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                            ->where('tipo', 'fechamento')
                            ->get();

        return view('fechamento_caixa.confirmacao_auditoria', compact('caixa', 'movimentacoes'));

    }

    public function show($auditoriaId)
    {
        // 1️⃣ Busca o cabeçalho da auditoria fiscal
        $auditoria = DB::table('auditorias_caixa')->findOrFail($auditoriaId);
        
        // Busca o caixa vinculado a essa auditoria
        $caixa = DB::table('caixas')->where('id', $auditoria->caixa_id)->first();

        // 2️⃣ TABELA 1: Lançamentos Manuais - Retiradas do Caixa (Sangrias, Despesas e Saídas)
        // Busca na fita de movimentações tudo o que reduziu o saldo do caixa
        $retiradas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $auditoria->caixa_id)
            ->whereIn('tipo', ['saida_manual', 'sangria', 'cancelamento_venda', 'despesa'])
            ->orderBy('id', 'asc')
            ->get();

        // 3️⃣ TABELA 2: Total de Valores Auditados (O pente-fino corrigido salvo em auditoria_detalhes!)
        // Faz um JOIN com a tabela de usuários para capturar o nome do Auditor por extenso
        $valoresAuditados = DB::table('auditoria_detalhes')
            ->join('auditorias_caixa', 'auditoria_detalhes.auditoria_id', '=', 'auditorias_caixa.id')
            ->join('users', 'auditorias_caixa.user_id', '=', 'users.id')
            ->where('auditoria_detalhes.auditoria_id', $auditoriaId)
            ->select(
                'auditoria_detalhes.forma_pagamento as forma',
                'auditoria_detalhes.total_fisico as valor', // ou valor_informado dependendo do seu banco
                'users.name as auditor_nome',
                'auditoria_detalhes.created_at as data'
            )
            ->get();

        return view('auditoria_caixa.show', compact('auditoria', 'caixa', 'retiradas', 'valoresAuditados'));
    }

}