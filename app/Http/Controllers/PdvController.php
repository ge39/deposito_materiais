<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\Caixa;
 use Carbon\Carbon;

class PDVController extends Controller
{
    public function __construct()
    {
        // Aplica middleware auth em todas as rotas do PDV
        $this->middleware('auth');
          
    }
    
    /**
     * Exibe a tela principal do PDV
     */

    // public function index(Request $request)
    // {
    //     // 1️⃣ Pegar o terminal do middleware
    //     $terminal = $request->attributes->get('terminal');

    //     if (!$terminal) {
    //         abort(500, 'Terminal não identificado no PDV.');
    //     }

    //     // 2️⃣ Pegar o caixa aberto mais recente deste terminal
    //     $caixaAberto = \App\Models\Caixa::with('usuario')
    //         ->where('terminal_id', $terminal->id)
    //         ->where('status', 'aberto')
    //         ->latest('data_abertura')
    //         ->first();

    //     // 3️⃣ Preparar dados complementares (opcional)
    //     $operador = $caixaAberto?->usuario?->name ?? 'Nenhum';
    //     $status = $caixaAberto ? 'Aberto' : 'Fechado';

    //     // 4️⃣ Retornar a view mantendo as variáveis originais + extras
    //     return view('pdv.index', [
    //         'terminal' => $terminal,
    //         'caixaAberto' => $caixaAberto,
    //         'operador' => $operador,
    //         'status' => $status,
    //     ]);
    // }

    public function index(Request $request)
    {
        // 1️⃣ Pegar o terminal do middleware
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            abort(500, 'Terminal não identificado no PDV.');
        }

        // 2️⃣ Pegar o caixa aberto mais recente deste terminal (mantendo a regra de bloqueio)
        $caixaAberto = \App\Models\Caixa::with('usuario')
            ->where('terminal_id', $terminal->id)
            // ->where('status', 'aberto') // mantém a validação original
            ->latest('data_abertura')
            ->first();

        // 3️⃣ Preparar dados complementares
        $operador = $caixaAberto?->usuario?->name ?? 'Nenhum';

        // 4️⃣ Determinar status considerando múltiplos casos do PDV
        $status = 'Fechado'; // padrão
        if ($caixaAberto) {
            switch ($caixaAberto->status) {
                case 'aberto':
                    $status = 'Aberto';
                    break;
                case 'pendente':
                    $status = 'Pendente';
                    break;
                case 'inconsistente':
                    $status = 'Inconsistente';
                    break;
                case 'fechado':
                default:
                    $status = 'Fechado';
            }
        }

        // 5️⃣ Retornar a view com todas as variáveis
        return view('pdv.index', [
            'terminal' => $terminal,
            'caixaAberto' => $caixaAberto,
            'operador' => $operador,
            'status' => $status,
        ]);
    }
        
   /**
     * F2 – Buscar Cliente (Modal de cliente) */
    public function buscarCliente(Request $request)
    {
        $query = $request->input('query');

        $clientes = Cliente::where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('nome', 'LIKE', "%{$query}%")
                  ->orWhere('tipo', 'LIKE', "%{$query}%")
                  ->orWhere('cpf_cnpj', 'LIKE', "%{$query}%")
                  ->orWhere('telefone', 'LIKE', "%{$query}%")
                  ->orWhere('endereco', 'LIKE', "%{$query}%")
                  ->orWhere('numero', 'LIKE', "%{$query}%")
                  ->orWhere('cep', 'LIKE', "%{$query}%")
                  ->orWhere('bairro', 'LIKE', "%{$query}%")
                  ->orWhere('cidade', 'LIKE', "%{$query}%")
                  ->orWhere('estado', 'LIKE', "%{$query}%");
            })
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome','tipo', 'cpf_cnpj', 'telefone', 'endereco', 'numero','cep','bairro','cidade','estado']);

        return response()->json($clientes);
    }
 
   /**
     * F3 – Buscar Produto (Modal de produtos) */        
    public function buscarProduto(Request $request)
    {
        $query = trim($request->input('query'));

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
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
                'imagem' => $p->imagem ? asset('storage/'.$p->imagem) : null,

                // quantidade total disponivel calculada a partir dos lotes carregados
                'quantidade_total_disponivel' => (int) $quantidadeTotal,
            ];
        });

        return response()->json($resultado);
    }

    /**
     *  Buscar Produto (Código de Barras) */
    // public function buscarProdutoPorCodigo($codigo)
    // {
    //     // 🔹 Validação básica
    //     if (empty($codigo)) {
    //         return response()->json([
    //             'status' => 'erro',
    //             'mensagem' => 'Código de produto não informado.'
    //         ], 400);
    //     }

    //     // 🔹 Autenticação (se necessário)
    //     if (!auth()->check()) {
    //         return response()->json([
    //             'status' => 'erro',
    //             'mensagem' => 'Usuário não autorizado.'
    //         ], 401);
    //     }

    //     // 🔹 Buscar produto ativo com lotes válidos
    //     $produto = Produto::with([
    //         'categoria',
    //         'marca',
    //         'unidadeMedida',
    //         'lotes' => function ($q) {
    //             $q->where('status', 1)
    //             ->where('quantidade_disponivel', '>', 0)
    //             ->whereDate('validade_lote', '>=', now());
    //         }
    //     ])->where('ativo', 1)
    //     ->where('codigo_barras', $codigo)
    //     ->first();

    //     if (!$produto) {
    //         return response()->json([
    //             'status' => 'erro',
    //             'mensagem' => 'Produto não encontrado.'
    //         ], 404);
    //     }

    //     // Adiciona a sigla da unidade diretamente no objeto
    //     $produto->unidade_sigla = $produto->unidadeMedida->sigla ?? null;

    //     // 🔹 Soma quantidade total disponível (opcional)
    //     $produto->quantidade_total_disponivel = $produto->lotes->sum('quantidade_disponivel');

    //     return response()->json([
    //         'status' => 'ok',
    //         'produto' => $produto
    //     ]);
    // }

    public function buscarProdutoPorCodigo($codigo)
    {
        // 🔹 Validação básica
        if (empty($codigo)) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Código de produto não informado.'
            ], 400);
        }

        // 🔹 Autenticação (se necessário)
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

        // 🔹 Unidade
        $produto->unidade_sigla = $produto->unidadeMedida->sigla ?? null;

        // 🔹 Soma quantidade total disponível
        $produto->quantidade_total_disponivel = $produto->lotes->sum('quantidade_disponivel');

        /*
        |--------------------------------------------------------------------------
        | 🔔 INFORMAÇÃO DE VALIDADE (APENAS INFORMATIVA)
        |--------------------------------------------------------------------------
        */
        $produto->alerta_validade = null;

        if ($produto->lotes->count() > 0) {

            // Lote com validade mais próxima
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
     * F4 – Buscar Vendas do dia (Histórico)*/
    public function buscarVendasDia()
    {
        $vendas = Venda::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'DESC')
            ->limit(30)
            ->get();

        return response()->json($vendas);
    }

    /**
     * F5 – Finalizar Venda */
    public function finalizarVenda(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|integer',
            'itens'      => 'required|array',
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Venda finalizada (estrutura básica criada).'
        ]);
    }

    /**
     * F6 – Cancelar Venda Atual */
    public function cancelarVenda()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Venda cancelada.'
        ]);
    }

    /**
     * F7 – Consultar Preço Rápido */
    public function consultarPreco(Request $request)
    {
        $codigo = $request->input('codigo');

        $produto = Produto::where(function($q) use ($codigo) {
                $q->where('codigo_barras', $codigo)
                  ->orWhere('sku', $codigo);
            })
            ->where('ativo', 1)
            ->whereHas('lotes', function($q) {
                $q->where('status', 1)
                  ->where('quantidade_disponivel', '>', 0)
                  ->whereDate('validade_lote', '>=', now());
            })
            ->first();

        return response()->json($produto);
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
       
    public function caixasEsquecidos(int $horasLimite = 12)
    {
        $agora = Carbon::now('America/Sao_Paulo');
        $limite = (clone $agora)->subHours($horasLimite);

        $caixas = Caixa::where('status', 'aberto')
            ->where('data_abertura', '<', $limite)
            ->with('usuario')
            ->get()
            ->map(function ($caixa) use ($agora) {

                $abertura = Carbon::parse($caixa->data_abertura)
                    ->setTimezone('America/Sao_Paulo');

                // ⚠️ FORÇANDO horas inteiras (não existe decimal aqui)
                $caixa->pdv_horas_aberto = (int) $abertura->diffInHours($agora);

                // Datas formatadas PT-BR
                $caixa->data_abertura_br = $abertura->format('d/m/Y H:i');
                $caixa->data_fechamento_br = $caixa->data_fechamento
                    ? Carbon::parse($caixa->data_fechamento)
                        ->setTimezone('America/Sao_Paulo')
                        ->format('d/m/Y H:i')
                    : null;

                return $caixa;
            });

        return response()->json($caixas);
    }
    
}


