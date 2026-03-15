<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
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
   
//    public function index(Request $request)
//     {
//         // 1️⃣ Cliente padrão "VENDA BALCAO"
//         $clienteBalcao = Cliente::where('nome', 'VENDA BALCAO')
//             ->where('ativo', 1)
//             ->firstOrFail();
//             $clienteId   = $clienteBalcao->id;

//         // 2️⃣ Terminal identificado pelo middleware
//         $terminal = $request->attributes->get('terminal');

//         if (!$terminal) {
//             abort(500, 'Terminal não identificado no PDV.');
//         }

//         // 2️⃣ Busca o CAIXA ABERTO vinculado ao terminal
//         $caixaAberto = Caixa::where('terminal_id', $terminal->id)
//             ->where('status', 'aberto')
//             ->latest('data_abertura')
//             ->first();

//         if (!$caixaAberto) {
//         // Nenhum caixa aberto: redireciona para abertura de caixa
//         return redirect()->route('caixa.abrir')
//                             ->with('info', 'Nenhum caixa aberto. Abra um caixa para continuar.');
//         }

//         // Continua normalmente com o PDV
//         // 4️⃣ Operador
//         $operadorId   = $caixaAberto?->usuario?->id ?? null;
//         $operador = $caixaAberto?->usuario?->name ?? 'Nenhum';
//         $caixa_id = $caixaAberto->id;

//         // 5️⃣ Status do caixa
//         $status = 'Fechado';
//         if ($caixaAberto) {
//             switch ($caixaAberto->status) {
//                 case 'aberto':
//                     $status = 'Aberto';
//                     break;
//                 case 'pendente':
//                     $status = 'Pendente';
//                     break;
//                 case 'inconsistente':
//                     $status = 'Inconsistente';
//                     break;
//                 default:
//                     $status = 'Fechado';
//             }
//         }

//         // 6️⃣ ÚNICO return
//         return view('pdv.index', [
//             'clienteBalcao' => $clienteBalcao,
//             'terminal'      => $terminal,
//             'caixaAberto'   => $caixaAberto,
//             'caixa'         => $caixaAberto, // ✅ agora $caixa também está disponível
//             'caixa_id'      => $caixa_id,
//             'operador'      => $operador,
//             'status'        => $status,
//             'operadorId'    => $operadorId,
//         ]);
//     }

     public function index(Request $request)
    {
        // 1️⃣ Cliente padrão
        $clienteBalcao = Cliente::select(
            'id','nome','tipo','telefone','endereco','endereco_entrega'
        )->where('nome','VENDA BALCAO')
        ->where('ativo',1)
        ->first();

        if (!$clienteBalcao) {
            abort(403, 'O cliente "VENDA BALCAO" precisa estar ativo.');
        }

        // 2️⃣ Terminal identificado pelo middleware
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            abort(403, 'Terminal não identificado.');
        }

        // 3️⃣ Busca caixa aberto no terminal
        $caixa = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->latest('data_abertura')
            ->first();

        if (!$caixa) {
            return redirect()
                ->route('caixa.abrir')
                ->with('info', 'Nenhum caixa aberto. Abra um caixa para continuar.');
        }

        // 4️⃣ Operador
        $operadorId = $caixa->usuario?->id;
        $operador   = $caixa->usuario?->name ?? 'Nenhum';

        // 5️⃣ Status formatado
        $status = match ($caixa->status) {
            'aberto'        => 'Aberto',
            'pendente'      => 'Pendente',
            'inconsistente' => 'Inconsistente',
            default         => 'Fechado',
        };

        // 6️⃣ Verificação de sangria
        $verificacao = $caixa->verificarSangria();

        $saldoAtual         = $verificacao['saldoAtual'];
        $limiteSangria      = $verificacao['limiteSangria'];
        $avisarSangria      = $verificacao['avisarSangria'];
        $bloquearPDV        = $verificacao['bloquearPDV'];

        if ($bloquearPDV) {
            return redirect()
                ->route('caixa.sangria.form', ['caixa' => $caixa->id])
                ->with('error', 'Limite de dinheiro excedido. Realize a sangria para continuar.');
        }

        // 7️⃣ Retorna view
        return view('pdv.index', [
            'clienteBalcao'  => $clienteBalcao,
            'tipo'          => $clienteBalcao->tipo,
            'telefone'       => $clienteBalcao->telefone,
            'terminal'       => $terminal,
            'caixaAberto'    => $caixa,
            'caixa'          => $caixa,
            'caixa_id'       => $caixa->id,
            'operador'       => $operador,
            'operadorId'     => $operadorId,
            'status'         => $status,
            'saldoAtual'     => $saldoAtual,
            'limiteSangria'  => $limiteSangria,
            'avisarSangria'  => $avisarSangria,
            'bloquearPDV'    => $bloquearPDV,
        ]);
    }
        
   /**
     * F2 – Buscar Cliente (Modal de cliente) */
    public function buscarCliente(Request $request)
    {
        $query = $request->input('query');

        $clientes = Cliente::where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('bairro', 'LIKE', "%{$query}%")
                  ->orWhere('tipo', 'LIKE', "%{$query}%")
                  ->orWhere('cpf_cnpj', 'LIKE', "%{$query}%")
                  ->orWhere('telefone', 'LIKE', "%{$query}%")
                  ->orWhere('endereco', 'LIKE', "%{$query}%")
                  ->orWhere('numero', 'LIKE', "%{$query}%")
                  ->orWhere('cep', 'LIKE', "%{$query}%")
                 ->orWhere('estado', 'LIKE', "%{$query}%")
                  ->orWhere('cidade', 'LIKE', "%{$query}%")
                  ->orWhere('nome', 'LIKE', "%{$query}%");
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

        // Exibe a tela de finalizar (F6)
    public function finalizar(Request $request)
    {
        $cliente = session('pdv_cliente'); // Cliente selecionado no PDV
        $carrinho = session('pdv_carrinho', []); // Carrinho com produtos e quantidades
        $total = 0;

        foreach ($carrinho as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }
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

    public function verificarSangria(): array
    {
        $empresa = $this->empresa()->with('configuracaoCaixa')->first();

        $limite = $empresa?->configuracaoCaixa?->limite_sangria ?? 0;

        if (!$empresa || !$empresa->configuracaoCaixa) {
            return [
                'saldoAtual'     => 0.0,
                'limiteSangria'  => 0.0,
                'limiteBloqueio' => 0.0,
                'avisarSangria'  => false,
                'bloquearPDV'    => false,
                
            ];
        }

        $config = $empresa->configuracaoCaixa;
        $limite        = (float) $config->limite_sangria;      // 500
        $percentual    = (float) $config->percentual_bloqueio; // 50
        $bloqueioAtivo = (bool)  $config->bloqueio_ativo;      // 1
        $saldoAtual = $this->saldoDinheiroAtual(); // Deve retornar 850
        $valorSugeridoSangria = max(0, $saldoAtual - $limite);
        $limiteBloqueio = $limite * (1 + ($percentual / 100)); // 750

        
             
        return [
            'saldoAtual'     => $saldoAtual,
            'limiteSangria'  => $limite,
            'limiteBloqueio' => $limiteBloqueio,
            'avisarSangria'  => $saldoAtual >= $limite,
            'bloquearPDV'    => $bloqueioAtivo && $saldoAtual >= $limiteBloqueio,
            'valorSugeridoSangria' =>$valorSugeridoSangria,
            
        ];
    }
}


