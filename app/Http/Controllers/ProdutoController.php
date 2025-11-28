<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use App\Models\Marca;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->nivel_acesso, ['admin','gerente'])) {
                abort(403, 'Acesso negado!');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $produtos = Produto::with(['categoria','fornecedor','marca','unidadeMedida'])
            ->where('ativo', 1)
            ->paginate(15);

        return view('produtos.index', compact('produtos'));
    }

    // public function create()
    // {
    //     return view('produtos.create', [
    //         'categorias' => Categoria::where('ativo','1')->get(),
    //         'fornecedores' => Fornecedor::where('ativo','1')->get(),
    //         'unidades' => UnidadeMedida::where('ativo','1')->get(),
    //         'marcas' => Marca::where('ativo','1')->get(),

    //         // ➕ ADICIONE ESTA LINHA
    //         'produtosExistentes' => Produto::select('id','nome')->get(),
    //     ]);
    // }

    public function create()
    {
        // Carrega apenas registros ativos
        $categorias = Categoria::where('ativo', 1)->get();
        $fornecedores = Fornecedor::where('ativo', 1)->get();
        $unidades = UnidadeMedida::where('ativo', 1)->get();
        $marcas = Marca::where('ativo', 1)->get();

        // Lista de produtos existentes com quantidade disponível calculada
        $produtosExistentes = Produto::with(['lotes' => function($query) {
            $query->where('status', 'ativo')->where('quantidade', '>', 0);
        }])->get()->map(function($produto) {
            $produto->quantidade_disponivel = $produto->lotes->sum('quantidade');
            return $produto;
        });

        return view('produtos.create', compact(
            'categorias',
            'fornecedores',
            'unidades',
            'marcas',
            'produtosExistentes'
        ));
    }

     public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'               => 'required|string|max:255',
            'sku'                => 'nullable|string|max:255',
            'descricao'          => 'nullable|string|max:255',
            'categoria_id'       => 'required|integer',
            'fornecedor_id'      => 'required|integer',
            'marca_id'           => 'nullable|integer',
            'unidade_medida_id'  => 'required|integer',
            'codigo_barras'      => 'nullable|string|max:255',
            'preco_venda'        => 'required|numeric',
            'preco_custo'        => 'required|numeric',
            'quantidade_estoque' => 'required|integer|min:1',
            'data_compra'        => 'required|date',
            'validade_produto'   => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // 🔹 BUSCA PRODUTO EXISTENTE COM BLOQUEIO
            $produto = Produto::where('nome', $validated['nome'])
                ->where('sku', $validated['sku'])
                ->where('marca_id', $validated['marca_id'])
                ->where('categoria_id', $validated['categoria_id'])
                ->where('unidade_medida_id', $validated['unidade_medida_id'])
                ->lockForUpdate()
                ->first();

            if ($produto) {
                // 🔹 PRODUTO EXISTENTE: atualiza campos
                $produto->descricao     = $validated['descricao'];
                $produto->codigo_barras = $validated['codigo_barras'];
                $produto->preco_custo   = $validated['preco_custo'];
                $produto->preco_venda   = $validated['preco_venda'];
                $produto->save();
            } else {
                // 🔹 NOVO PRODUTO
                $produto = Produto::create([
                    'nome'               => $validated['nome'],
                    'sku'                => $validated['sku'],
                    'marca_id'           => $validated['marca_id'],
                    'categoria_id'       => $validated['categoria_id'],
                    'unidade_medida_id'  => $validated['unidade_medida_id'],
                    'descricao'          => $validated['descricao'],
                    'fornecedor_id'      => $validated['fornecedor_id'],
                    'codigo_barras'      => $validated['codigo_barras'],
                    'preco_custo'        => $validated['preco_custo'],
                    'preco_venda'        => $validated['preco_venda'],
                    'quantidade_estoque' => 0, // inicializamos zero, será atualizado pelo lote
                   
                    'estoque_minimo'     => 0,
                    'ativo'              => 1,
                    'validade_produto'   => $validated['validade_produto'] ?? null,
                ]);
            }

            // 🔹 CRIA O LOTE
            $lote = $produto->lotes()->create([
                'numero_lote'           => 'L' . time(),
                'pedido_compra_id'      => null,
                'produto_id'            => $produto->id,
                'fornecedor_id'         => $validated['fornecedor_id'],
                'quantidade'            => $validated['quantidade_estoque'],
                'quantidade_disponivel' => $validated['quantidade_estoque'],
                'preco_compra'          => $validated['preco_custo'],
                'data_compra'           => $validated['data_compra'],
                'validade_lote'         => $validated['validade_produto'] ?? null,
            ]);

            // 🔹 ATUALIZA ESTOQUE TOTAL DO PRODUTO AUTOMATICAMENTE
            $produto->quantidade_estoque = $produto->lotes()->sum('quantidade');
            // $produto->estoque_total       = $produto->quantidade_estoque;
            $produto->save();

            DB::commit();

            return redirect()->route('produtos.index')
                            ->with('success', 'Produto e lote salvos com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Erro ao salvar: ' . $e->getMessage());
        }
    }

    // public function edit(Produto $produto)
    // {
    //     return view('produtos.edit', [
    //         'produto' => $produto,
    //         'categorias' => Categoria::all(),
    //         'fornecedores' => Fornecedor::all(),
    //         'unidades' => UnidadeMedida::all(),
    //         'marcas' => Marca::all(),
    //     ]);
    // }
        /** FORMULÁRIO DE EDIÇÃO */
    public function edit(Produto $produto)
    {
        // Verifica se outro usuário está editando
        if ($produto->editando_por && $produto->editando_por != auth()->id()) {
            $usuario = \App\Models\User::find($produto->editando_por);
            $nome = $usuario ? $usuario->name : 'Outro usuário';
            return redirect()->route('produtos.index')
                            ->with('error', "Este produto já está sendo editado por $nome.");
        }

        // Marca como sendo editado pelo usuário atual
        $produto->editando_por = auth()->id();
        $produto->editando_em = now();
        $produto->save();

        return view('produtos.edit', [
            'produto' => $produto,
            'categorias' => Categoria::all(),
            'fornecedores' => Fornecedor::all(),
            'unidades' => UnidadeMedida::all(),
            'marcas' => Marca::all(),
        ]);
    }

    // public function update(Request $request, Produto $produto)
    // {
    //     $this->validateProduto($request, false);

    //     DB::transaction(function () use ($request, $produto) {

    //         $produto->fill($request->except('imagem'));

    //             // Verifica flag de edição
    //         if ($produto->editando_por && $produto->editando_por != auth()->id()) {
    //             $usuario = $produto->usuarioEditando; 
    //             $nomeUsuario = $usuario->name ?? 'Outro usuário';

    //             // Ao invés de lançar Exception, redirecione com erro
    //             return redirect()
    //                 ->route('produtos.index')
    //                 ->with('error', "Este produto está sendo editado por: {$nomeUsuario}");
    //         }

    //         if ($request->filled('validade_produto')) {
    //             $produto->validade_produto = Carbon::parse($request->validade_produto)->startOfDay();
    //         }

    //         if ($request->hasFile('imagem')) {
    //             if ($produto->imagem) {
    //                 Storage::disk('public')->delete($produto->imagem);
    //             }
    //             $produto->imagem = $request->file('imagem')->store('produtos', 'public');
    //         }

    //         // --- Libera o bloqueio de edição ---
    //         if ($produto->editando_por == auth()->id()) {
    //             $produto->editando_por = null;
    //             $produto->editando_em = null;
    //             $produto->save();
    //         }

    //         $produto->save();
    //     });


    //     return redirect()->route('produtos.index')
    //         ->with('success', 'Produto atualizado com sucesso!');
    // }

    public function update(Request $request, Produto $produto)
    {
        $this->validateProduto($request, false);

        // --- Verifica bloqueio ANTES da transação ---
        if ($produto->editando_por && $produto->editando_por != auth()->id()) {
            $usuario = $produto->usuarioEditando;
            $nomeUsuario = $usuario->name ?? 'Outro usuário';

            return redirect()
                ->route('produtos.index')
                ->with('error', "Este produto está sendo editado por: {$nomeUsuario}");
        }

        DB::transaction(function () use ($request, $produto) {

            $produto->fill($request->except('imagem'));

            if ($request->filled('validade_produto')) {
                $produto->validade_produto = Carbon::parse($request->validade_produto)->startOfDay();
            }

            if ($request->hasFile('imagem')) {
                if ($produto->imagem) {
                    Storage::disk('public')->delete($produto->imagem);
                }
                $produto->imagem = $request->file('imagem')->store('produtos', 'public');
            }

            // --- Libera o bloqueio de edição ---
            if ($produto->editando_por == auth()->id()) {
                $produto->editando_por = null;
                $produto->editando_em = null;
            }

            $produto->save();
        });

        return redirect()->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function inativos()
    {
        $produtos = Produto::where('ativo',0)->paginate(15);
        return view('produtos.inativos', compact('produtos'));
    }

    public function desativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 0;
        $produto->save();

        return redirect()->route('produtos.index')
            ->with('success','Produto desativado com sucesso!');
    }

    public function reativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 1;
        $produto->save();

        return redirect()->route('produtos.inativos')
            ->with('success','Produto ativado com sucesso!');
    }

    protected function validateProduto(Request $request, bool $full = true)
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'unidade_medida_id' => 'required|exists:unidades_medida,id',
            'marca_id' => 'required|exists:marcas,id',
            'validade_produto' => 'nullable|date',
            'imagem' => 'nullable|image|max:2048',
        ];

        if ($full) {
            $rules = array_merge($rules, [
                'sku' => 'nullable|string|max:50',
                'codigo_barras' => 'nullable|string|max:50',
                'quantidade_estoque' => 'required|numeric|min:0',
                'estoque_minimo' => 'required|numeric|min:0',
                'preco_custo' => 'required|numeric|min:0',
                'preco_venda' => 'required|numeric|min:0',
                'data_compra' => 'required|date',
            ]);
        }

        $request->validate($rules);
    }
     public function indexGrid()
    {
       // CORRETO: retorna LengthAwarePaginator
    $produtos = Produto::where('ativo', 1)->paginate(15);

    return view('produtos.index-grid', compact('produtos'));
    }
    /** EXIBIR PRODUTO */
    public function show($id)
    {
        $produto = Produto::findOrFail($id);
        return view('produtos.show', compact('produto'));
    }
     /** LIMPAR FLAG DE EDIÇÃO */
    public function limparEdicao($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->editando_por = null;
        $produto->editando_em = null;
        $produto->save();

        return response()->json(['status' => 'ok']);
    }
}
