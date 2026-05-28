<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Empresa;
use App\Models\Caixa;
use Carbon\Carbon;

class PdvController extends Controller
{
    public function __construct()
    {
        // Aplica middleware auth em todas as rotas do PDV
        $this->middleware('auth');
          
    }
   

    // public function index(Request $request)
    // {
    //     // 1️⃣ Cliente padrão
    //     $clienteBalcao = Cliente::select(
    //         'id','nome','tipo','telefone','endereco','endereco_entrega'
    //     )->where('nome','VENDA BALCAO')
    //     ->where('ativo',1)
    //     ->first();

    //     if (!$clienteBalcao) {
    //         abort(403, 'O cliente "VENDA BALCAO" precisa estar ativo.');
    //     }

    //     // 2️⃣ Terminal identificado pelo middleware
    //     $terminal = $request->attributes->get('terminal');

    //     if (!$terminal) {
    //         abort(403, 'Terminal não identificado.');
    //     }

    //     // 3️⃣ Busca caixa aberto no terminal
    //     $caixa = Caixa::where('terminal_id', $terminal->id)
    //         ->where('status', 'aberto')
    //         ->latest('data_abertura')
    //         ->first();

    //     if (!$caixa) {
    //         return redirect()
    //             ->route('caixa.abrir')
    //             ->with('info', 'Nenhum caixa aberto. Abra um caixa para continuar.');
    //     }

    //     // 4️⃣ Operador
    //     $operadorId = $caixa->usuario?->id;
    //     $operador   = $caixa->usuario?->name ?? 'Nenhum';

    //     // 5️⃣ Status formatado
    //     $status = match ($caixa->status) {
    //         'aberto'        => 'Aberto',
    //         'pendente'      => 'Pendente',
    //         'inconsistente' => 'Inconsistente',
    //         default         => 'Fechado',
    //     };

    //     // 6️⃣ Verificação de sangria
    //     $verificacao = $caixa->verificarSangria();

    //     $saldoAtual         = $verificacao['saldoAtual'];
    //     $limiteSangria      = $verificacao['limiteSangria'];
    //     $avisarSangria      = $verificacao['avisarSangria'];
    //     $bloquearPDV        = $verificacao['bloquearPDV'];

    //     // if ($bloquearPDV) {
    //     //     return redirect()
    //     //         ->route('caixa.sangria.form', ['caixa' => $caixa->id])
    //     //         ->with('error', 'Limite de dinheiro excedido. Realize a sangria para continuar.');
    //     // }

    //        // 7️⃣ Retorna view
    //     return view('pdv.index', [
    //         'clienteBalcao'        => $clienteBalcao,
    //         'tipo'                 => $clienteBalcao->tipo,
    //         'telefone'             => $clienteBalcao->telefone,
    //         'terminal'             => $terminal,
    //         'caixaAberto'          => $caixa,
    //         'caixa'                => $caixa,
    //         'caixa_id'             => $caixa->id,
    //         'operador'             => $operador,
    //         'operadorId'           => $operadorId,
    //         'status'               => $status,
    //         'saldoAtual'           => $saldoAtual,
            
    //         // Envia as duas variações de nome para garantir compatibilidade com o Blade
    //         'limiteSangria'        => $limiteSangria, // camelCase
    //         'limite_sangria'       => $limiteSangria, // snake_case 👈 Seu Blade deve estar procurando esta
            
    //         // Força booleano estrito: só ativa se o backend mandou true E o saldo for maior que zero
    //         'avisarSangria'        => $avisarSangria && ($saldoAtual > 0),
    //         'bloquearPDV'          => $bloquearPDV && ($saldoAtual > 0),
    //         'valorSugeridoSangria' => $verificacao['valorSugeridoSangria'],
    //     ]);
    // }

    public function index(Request $request)
    {
        // 1️⃣ Cliente padrão (Venda Balcão ID 6 do banco)
        $clienteBalcao = Cliente::select(
            'id', 'nome', 'tipo', 'telefone', 'endereco', 'endereco_entrega'
        )->where('nome', 'VENDA BALCAO')
        ->where('ativo', 1)
        ->first();

        if (!$clienteBalcao) {
            abort(403, 'O cliente "VENDA BALCAO" precisa estar ativo no cadastro.');
        }

        // 2️⃣ Terminal identificado via Middleware do XAMPP
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            abort(403, 'Terminal operacional não identificado no sistema.');
        }

        // 3️⃣ Busca o caixa que se encontra com o status 'aberto' neste terminal
        $caixa = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->latest('data_abertura')
            ->first();

        if (!$caixa) {
            return redirect()
                ->route('caixa.abrir')
                ->with('info', 'Nenhum caixa aberto. Abra um caixa para continuar.');
        }

        // 4️⃣ Vinculação do Operador logado na sessão
        $operadorId = $caixa->usuario?->id ?? auth()->id();
        $operador   = $caixa->usuario?->name ?? 'Não identificado';

        // 5️⃣ Status formatado para a cor do cabeçalho do PDV
        $status = match ($caixa->status) {
            'aberto'        => 'Aberto',
            'pendente'      => 'Pendente',
            'inconsistente' => 'Inconsistente',
            default         => 'Fechado',
        };
        

        // 6️⃣ EXECUÇÃO DO APURADOR DE TETOS DE SANGRIA NO MODEL CAIXA
        $verificacao = $caixa->verificarSangria();

        $saldoAtual         = (float) $verificacao['saldoAtual']; // Volte para a consulta real
        $limiteSangria      = (float) $verificacao['limiteSangria'];
        $avisarSangria      = (bool)  $verificacao['avisarSangria']; // Volte para a consulta real
        $bloquearPDV        = (bool)  $verificacao['bloquearPDV'];


        // 7️⃣ Retorna a View enviando as chaves exatas sincronizadas com os Scripts da Blade
        return view('pdv.index', [
            'clienteBalcao'        => $clienteBalcao,
            'tipo'                 => $clienteBalcao->tipo,
            'telefone'             => $clienteBalcao->telefone,
            'terminal'             => $terminal,
            'caixaAberto'          => $caixa,
            
            // Injeta as duas variações de objeto para garantir que o Eloquent e o JS leiam o ID
            'caixa'                => $caixa,
            'caixa_id'             => (int) $caixa->id, 
            
            'operador'             => $operador,
            'operadorId'           => $operadorId,
            'status'               => $status,
            'saldoAtual'           => $saldoAtual,
            
            // Dobra os padrões para satisfazer buscas snake_case e camelCase na Blade
            'limiteSangria'        => $limiteSangria,
            'limite_sangria'       => $limiteSangria, 
            
            // 🌟 CORREGIDO: Remove a trava do saldo > 0 para permitir que caixas esquecidos vazios bloqueiem o PDV!
            'avisarSangria'        => $avisarSangria,
            'bloquearPDV'          => $bloquearPDV,
            'valorSugeridoSangria' => (float) $verificacao['valorSugeridoSangria'],
        ]);
    }

    public function verificarSangria(): array
    {
        // 1. Obtém o saldo real em dinheiro deduzido de sangrias anteriores
        $saldoAtual = $this->saldoDinheiroAtual();
        
        // 2. Busca a configuração amarrada à empresa deste caixa
        $config = \App\Models\SangriaConfig::where('empresa_id', $this->empresa_id)->first();

        // 3. Fallback de Segurança: se não achar para esta filial, pega a primeira configuração do banco
        if (!$config) {
            $config = \App\Models\SangriaConfig::first();
        }

        // 4. Se mesmo assim a tabela estiver vazia, assume valores padrão seguros
        if (!$config) {
            $limiteSangria = 200.00;
            $limiteBloqueio = 300.00;
        } else {
            // 5. MAPEAMENTO EXATO COM OS NOMES DAS SUAS COLUNAS DO BANCO
            $limiteSangria = (float) ($config->valor_limite ?? 200.00); 
            $limiteBloqueio = (float) ($config->valor_maximo_caixa ?? $limiteSangria); 
        }

        // 6. Regras lógicas de ativação baseadas no saldo real limpo da gaveta
        $deveAvisar = ($saldoAtual >= $limiteSangria) && ($saldoAtual > 0);
        $deveBloquear = ($saldoAtual >= $limiteBloqueio) && ($saldoAtual > 0);

        // 7. 🔥 MELHORIA CRUCIAL: Calcula a sugestão exata para fazer o caixa voltar ao limite seguro
        // Exemplo: Se o saldo é 1750 e o limite é 200, sugere sangrar 1550 para deixar exatamente 200 no caixa.
        $valorSugeridoSangria = $saldoAtual > $limiteSangria ? ($saldoAtual - $limiteSangria) : 0.00;

        // 8. 🔥 PREVISÃO DE AUDITORIA: Mapeia como ficará a gaveta (saldo_depois) caso a sangria sugerida seja aceita
        $saldoDepoisSugerido = max(0, $saldoAtual - $valorSugeridoSangria);

        return [
            'saldoAtual'            => round($saldoAtual, 2),            // Corresponde ao seu 'saldo_antes'
            'saldoDepoisSugerido'   => round($saldoDepoisSugerido, 2),   // Projeção real do seu 'saldo_depois'
            'limiteSangria'         => $limiteSangria,
            'limiteBloqueio'        => $limiteBloqueio,
            'avisarSangria'         => $deveAvisar,
            'bloquearPDV'           => $deveBloquear,
            'valorSugeridoSangria'  => round($valorSugeridoSangria, 2),
        ];
    }


   /**
     * F2 – Buscar Cliente (Modal de cliente) */
    
    public function buscarCliente(Request $request) 
    { 
        $query = $request->input('query'); 

        $clientes = Cliente::with([ 
            'creditoAtivo', 
            'ultimaMovimentacao' 
        ]) 
        ->whereIn('ativo', ['1', 'ativo']) 
        ->when($query, function ($q) use ($query) { 
            $q->where(function($sub) use ($query){ 
                $sub->where('nome', 'LIKE', "%{$query}%") 
                    ->orWhere('cpf_cnpj', 'LIKE', "%{$query}%"); 
            }); 
        }) 
        ->orderBy('nome') 
        ->limit(20) 
        ->get([ 
            'id', 'nome', 'tipo', 'cpf_cnpj', 'telefone', 'endereco', 'numero', 'cep', 'bairro', 'cidade', 'estado' 
        ]); 

        $clientes = $clientes->map(function ($cliente) { 
            // 1️⃣ Pega o limite de crédito (padrão é 0 se não houver registro)
            $limite = (float) (optional($cliente->creditoAtivo)->limite_credito ?? 0);
            
            // 2️⃣ Tenta pegar o saldo_apos da última movimentação
            $saldoApos = optional($cliente->ultimaMovimentacao)->saldo_apos;

            // 3️⃣ Se não houver movimentação (null), o saldo é o limite total
            $saldo = $saldoApos !== null ? (float) $saldoApos : $limite;

            // 4️⃣ Calcula o crédito que o cliente já utilizou
            $creditoUsado = $limite - $saldo;

            // 5️⃣ 🔥 NOVA REGRA: Captura o status do banco e força o bloqueio se o saldo for zero ou negativo
            $statusBanco = optional($cliente->creditoAtivo)->status ?? 'inativo';
            $statusFinal = ($statusBanco === 'bloqueado' || $saldo <= 0) ? 'bloqueado' : $statusBanco;

            return [ 
                // ========================= 
                // CLIENTE 
                // ========================= 
                'id' => $cliente->id, 
                'nome' => $cliente->nome, 
                'tipo' => $cliente->tipo, 
                'cpf_cnpj' => $cliente->cpf_cnpj, 
                'telefone' => $cliente->telefone, 
                'endereco' => $cliente->endereco, 
                'numero' => $cliente->numero, 
                'cep' => $cliente->cep, 
                'bairro' => $cliente->bairro, 
                'cidade' => $cliente->cidade, 
                'estado' => $cliente->estado, 

                // ========================= 
                // FINANCEIRO ATUALIZADO 
                // ========================= 
                'limite' => $limite, 
                'status' => $statusFinal, // 🔥 Retorna 'bloqueado' caso o saldo seja <= 0 ou status seja bloqueado no banco
                'saldo' => $saldo, 
                'credito_usado' => $creditoUsado > 0 ? $creditoUsado : 0, 

                // ========================= 
                // FORMAS 
                // ========================= 
                'formas' => [] 
            ]; 
        }); 

        return response()->json($clientes); 
    }

    /**
     * Obtém os dados financeiros consolidados de um cliente específico para o PDV.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function obterFinanceiro(int $id): JsonResponse
    {
        // 1️⃣ Carrega o cliente com as mesmas relações da busca
        $cliente = Cliente::with([
            'creditoAtivo',
            'ultimaMovimentacao'
        ])->find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado.'
            ], 404);
        }

        // 2️⃣ Pega o limite de crédito (padrão é 0 se não houver registro)
        $limite = (float) (optional($cliente->creditoAtivo)->limite_credito ?? 0);

        // 3️⃣ Tenta pegar o saldo_apos da última movimentação
        $saldoApos = optional($cliente->ultimaMovimentacao)->saldo_apos;

        // 4️⃣ Se não houver movimentação (null), o saldo é o limite total
        $saldo = $saldoApos !== null ? (float) $saldoApos : $limite;

        // 5️⃣ Calcula o crédito que o cliente já utilizou
        $creditoUsado = $limite - $saldo;

        // 6️⃣ 🔥 APLICANDO A MESMA REGRA: Bloqueia se o status for bloqueado no banco OU se o saldo for <= 0
        $statusBanco = optional($cliente->creditoAtivo)->status ?? 'inativo';
        $statusFinal = ($statusBanco === 'bloqueado' || $saldo <= 0) ? 'bloqueado' : $statusBanco;

        // 7️⃣ Retorna o JSON estruturado exatamente como o Front-end espera receber
        return response()->json([
            'success' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
            ],
            'limite' => $limite,
            'saldo' => $saldo,
            'credito_usado' => $creditoUsado > 0 ? $creditoUsado : 0,
            'status' => $statusFinal, // 🔥 Retorna 'bloqueado' de forma segura
            'formas_pagamento' => []  // Mantido para compatibilidade com o front
        ]);
    }
    
   /**
     * F3 – Buscar Produto (Modal de produtos) */        
    public function buscarProduto(Request $request)
    {
        $query = trim($request->input('query'));

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

          // 🔹 Autenticação (se necessário)
        if (!auth()->check()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autorizado.'
            ], 401);
        }

        // Carrega produtos com relacionamentos e lotes válidos (para cálculo de estoque)
        $produtos = Produto::with([
                'categoria',
                'marca',
                'unidadeMedida',
                'lotes' => function ($q) {
                    $q->where('status', 1)
                    ->where('quantidade_disponivel', '>', 0)
                    ->whereDate('validade_lote', '>=', now());
                }
            ])
            ->where('ativo', 1)
            ->where(function($q) use ($query) {
                $q->where('nome', 'LIKE', "%{$query}%")
                ->orWhere('sku', 'LIKE', "%{$query}%")
                ->orWhere('codigo_barras', 'LIKE', "%{$query}%");
            })
            ->limit(20)
            ->get();

        if ($produtos->isEmpty()) {
            return response()->json([]);
        }

        // Monta retorno simples e consistente para o front
        $resultado = $produtos->map(function ($p) {

            // soma dos lotes válidos para quantidade disponível
            $quantidadeTotal = $p->lotes->sum('quantidade_disponivel');

            return [
                'id' => $p->id,
                'nome' => $p->nome,
                'preco_venda' => $p->preco_venda,
                'sku' => $p->sku,
                'codigo_barras' => $p->codigo_barras,

                // marca como objeto compatível com seu JS (p.marca?.nome)
                'marca' => [
                    'nome' => $p->marca->nome ?? ''
                ],

                // unidade exposta diretamente (sigla)
                'unidade' => $p->unidadeMedida->sigla ?? ($p->unidadeMedida->nome ?? 'UN'),

                // imagem: transforma em URL pública se houver arquivo armazenado
                //  'imagem' => $p->imagem ? asset('storage/'.$p->imagem) : null,
          
                'imagem' => $p->imagem ? url('storage/'.$p->imagem) : url('images/produto-sem-imagem.png'),

                // quantidade total disponivel calculada a partir dos lotes carregados
                'quantidade_total_disponivel' => (int) $quantidadeTotal,
            ];
        });

          
        return response()->json($resultado);
    }
       // Buscar produto por codigo de barras
    public function buscarProdutoPorCodigo($codigo)
    {
        // 🔹 Validação básica
        if (empty($codigo)) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Código de produto não informado.'
            ], 400);
        }

        // 🔹 Autenticação
        if (!auth()->check()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autorizado.'
            ], 401);
        }

        // 🔹 Buscar produto ativo com lotes válidos
        $produto = Produto::with([
            'categoria',
            'marca',
            'unidadeMedida',
            'lotes' => function ($q) {
                $q->where('status', 1)
                ->where('quantidade_disponivel', '>', 0)
                ->whereDate('validade_lote', '>=', now());
            }
        ])
        ->where('ativo', 1)
        ->where('codigo_barras', $codigo)
        ->first();

        if (!$produto) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Produto não encontrado.'
            ], 404);
        }

        // 🔹 Unidade Dinâmica vinda do relacionamento com a tabela unidade_medidas
        $produto->unidade_sigla = $produto->unidadeMedida->sigla ?? null;

        // 🔹 Soma quantidade total disponível nos lotes ativos
        $produto->quantidade_total_disponivel = $produto->lotes->sum('quantidade_disponivel');

        // 🔔 Informação de validade informativa
        $produto->alerta_validade = null;

        if ($produto->lotes->count() > 0) {
            $loteMaisProximo = $produto->lotes->sortBy('validade_lote')->first();

            $diasParaVencer = now()->startOfDay()
                ->diffInDays(\Carbon\Carbon::parse($loteMaisProximo->validade_lote), false);

            if ($diasParaVencer < 0) {
                $produto->alerta_validade = 'Produto com lote vencido';
            } elseif ($diasParaVencer === 0) {
                $produto->alerta_validade = 'Produto vence hoje';
            } elseif ($diasParaVencer <= 30) {
                $produto->alerta_validade = "Produto vence em {$diasParaVencer} dias";
            }
        }
        
        return response()->json([
            'status' => 'ok',
            'produto' => $produto
        ]);
    }

   
    /**
     * F8 – Abrir Gaveta (apenas backend registra)
     */
    public function abrirGaveta()
    {
        return response()->json(['status' => 'gaveta-aberta']);
    }

    /**
     * F9 – Descontos / Cupons */
    public function aplicarDesconto(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Desconto processado (estrutura criada).'
        ]);
    }

    /**   * F10 – Pagamentos alternativos*/
    public function pagamentosAlternativos(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Pagamento alternativo recebido.'
        ]);
    }

    /**
     * Retorna todos os caixas abertos esquecidos
     * @param int $diasLimite Quantos dias considerar para "esquecido"
     * @return \Illuminate\Support\Collection
     */

    // 1. Adicione o parâmetro Request para capturar o terminal atual
        // 1. Adicione o parâmetro Request para capturar o terminal atual
    public function caixasEsquecidos(Request $request, int $horasLimite = 12)
    {
        $agora = Carbon::now('America/Sao_Paulo');
        $empresaId = auth()->user()->empresa_id ?? null;

        // 🎯 O SEGREDO ESTÁ AQUI: Captura o terminal direto da Session/Cookie injetada pelo seu Middleware
        // Substitua 'terminal_id' pelo nome exato da chave que o seu IdentificaTerminal.php usa
        $terminalId = session('terminal_id') ?? cookie('terminal_id') ?? $request->query('terminal_id');

        // Buscamos os caixas abertos da empresa logada
        $query = Caixa::where('status', 'aberto');
        
        // 🎯 FILTRO AUTOMÁTICO: Se o middleware identificou o terminal, filtra o banco por ele
        if ($terminalId) {
            $query->where('terminal_id', $terminalId);
        } else {
            // Se o middleware não encontrou o terminal na sessão, assume o 10 para o seu ambiente de teste rodar
            $query->where('terminal_id', 10);
        }

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        $caixas = $query->with(['usuario']) 
            ->get()
            ->map(function ($caixa) use ($agora) {

                // Força a leitura correta da data inserida no banco de dados
                $abertura = Carbon::parse($caixa->data_abertura, 'America/Sao_Paulo');

                // Calcula a diferença exata de horas
                $horasAberto = (int) $abertura->diffInHours($agora);
                
                $caixa->pdv_horas_aberto = $horasAberto;
                $caixa->data_abertura_br = $abertura->format('d/m/Y H:i');
                $caixa->nome_operador    = $caixa->usuario->name ?? '---';
                
                return $caixa;
            })
            // Filtra apenas os caixas que estouraram o limite de tempo configurado
            ->filter(function ($caixa) use ($horasLimite) {
                return $caixa->pdv_horas_aberto >= $horasLimite;
            })
            ->values();

        return response()->json($caixas);
    }
}


