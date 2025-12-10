<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;

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
    public function index()
    {
        return view('pdv.index');
    }

    /**
     * F2 – Buscar Cliente (Modal de cliente)
     */
    public function buscarCliente(Request $request)
    {
        $query = $request->input('query');

        $clientes = Cliente::where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('nome', 'LIKE', "%{$query}%")
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
            ->get(['id', 'nome', 'cpf_cnpj', 'telefone', 'endereco', 'numero','cep','bairro','cidade','estado']);

        return response()->json($clientes);
    }

   
   /**
     * F3 – Buscar Produto (Modal de produtos)
     */
        
//    public function buscarProduto(Request $request)
//     {
//         $query = trim($request->input('query'));

//         if (!$query || strlen($query) < 2) {
//             return response()->json([]);
//         }

//         $produtos = Produto::with(['categoria','marca','unidadeMedida'])
//             ->where('ativo', 1)
//             ->whereHas('lotes', function($q) {
//                 $q->where('status', 1)
//                 ->where('quantidade_disponivel', '>', 0)
//                 ->whereDate('validade_lote', '>=', now());
//             })
//             ->when($query, function($q) use ($query) {
//                 $q->where(function($inner) use ($query) {
//                     $inner->where('nome', 'LIKE', "%{$query}%")
//                         ->orWhere('sku', 'LIKE', "%{$query}%")
//                         ->orWhere('codigo_barras', 'LIKE', "%{$query}%");
//                 });
//             })
//             ->orderBy('nome')
//             ->limit(20)
//             ->get()
//             ->map(function($p) {

//                 // retorna exatamente o que seu JS espera
//                 return [
//                     'id'            => $p->id,
//                     'nome'          => $p->nome,
//                     'sku'           => $p->sku,
//                     'codigo_barras' => $p->codigo_barras,
//                     'preco_venda'   => $p->preco_venda,

//                     'marca' => [
//                         'nome' => $p->marca->nome ?? ''
//                     ],

//                     'unidade' => $p->unidadeMedida->sigla ?? 'UN',

//                     'imagem' => $p->imagem ? asset('storage/'.$p->imagem) : null
//                 ];
//             });

//         return response()->json($produtos);
//     }

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
     * F3.1 – Buscar Produto por Código de Barras
     */
    // public function buscarProdutoPorCodigo($codigo)
    // {
    //     if (!$codigo) {
    //         return response()->json([
    //             'status' => 'erro',
    //             'mensagem' => 'Código não informado.'
    //         ], 400);
    //     }

    //     try {
    //         $produto = Produto::with([
    //             'categoria',
    //             'marca',
    //             'unidadeMedida',
    //             'lotes' => function($q) {
    //                 $q->where('status', 1)
    //                 ->where('quantidade_disponivel', '>', 0)
    //                 ->whereDate('validade_lote', '>=', now())
    //                 ->orderBy('validade_lote', 'asc')
    //                 ->limit(1);
    //             }
    //         ])
    //         ->where('ativo', 1)
    //         ->where('codigo_barras', $codigo) // somente o campo código de barras
    //         ->first();

    //         if (!$produto) {
    //             return response()->json([
    //                 'status' => 'erro',
    //                 'mensagem' => 'Produto não encontrado.'
    //             ], 404);
    //         }

    //         return response()->json([
    //             'status' => 'ok',
    //             'produto' => $produto
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'erro',
    //             'mensagem' => 'Erro ao buscar produto.',
    //             'detalhes' => $e->getMessage()
    //         ], 500);
    //     }
    // }

     /**
     *  Buscar Produto (Código de Barras)
     */
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
        ])->where('ativo', 1)
        ->where('codigo_barras', $codigo)
        ->first();

        if (!$produto) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Produto não encontrado.'
            ], 404);
        }

        // 🔹 Soma quantidade total disponível (opcional)
        $produto->quantidade_total_disponivel = $produto->lotes->sum('quantidade_disponivel');

        return response()->json([
            'status' => 'ok',
            'produto' => $produto
        ]);
    }

    /**
     * F4 – Buscar Vendas do dia (Histórico)
     */
    public function buscarVendasDia()
    {
        $vendas = Venda::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'DESC')
            ->limit(30)
            ->get();

        return response()->json($vendas);
    }

    /**
     * F5 – Finalizar Venda
     */
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
     * F6 – Cancelar Venda Atual
     */
    public function cancelarVenda()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Venda cancelada.'
        ]);
    }

    /**
     * F7 – Consultar Preço Rápido
     */
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
     * F9 – Descontos / Cupons
     */
    public function aplicarDesconto(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Desconto processado (estrutura criada).'
        ]);
    }

    /**
     * F10 – Pagamentos alternativos
     */
    public function pagamentosAlternativos(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Pagamento alternativo recebido.'
        ]);
    }
}
