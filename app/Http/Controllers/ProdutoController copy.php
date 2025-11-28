<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Promocao;
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

        // Bloqueio de acesso: apenas admin e gerente
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!in_array($user->nivel_acesso, ['admin', 'gerente'])) {
                abort(403, 'Acesso negado!');
            }
            return $next($request);
        });
    }

    public function promocao()
    {
        return $this->hasOne(Promocao::class, 'produto_id')
            ->where('status', 1)
            ->whereDate('promocao_inicio', '<=', now())
            ->whereDate('promocao_fim', '>=', now());
    }

    /** LISTAR PRODUTOS ATIVOS */
    public function index()
    {
        $produtos = Produto::with(['lotes','categoria','fornecedor','marca','unidadeMedida','promocao'])
            ->where('ativo', 1)
            ->paginate(15);
        

        return view('produtos.index', compact('produtos'));
    }

    /** FORMULÁRIO DE CRIAÇÃO */
    public function create()
    {
        return view('produtos.create', [
            'categorias' => Categoria::where('ativo', '1')->get(),
            'fornecedores' => Fornecedor::where('Ativo', '1')->get(),
            'unidades' => UnidadeMedida::where('ativo', '1')->get(),
            'marcas' => Marca::where('ativo', '1')->get(),
            'produtosExistentes' => Produto::where('ativo', '1')->get(),
        ]);
    }

    /** SALVAR NOVO PRODUTO / ATUALIZAR ESTOQUE SE EXISTIR */
    // public function store(Request $request)
    // {
    //      $this->validateProduto($request);

    //     DB::transaction(function () use ($request) {

    //         $produto = Produto::where('nome', $request->nome)
    //             ->where('sku', $request->sku)
    //             ->where('marca_id', $request->marca_id)
    //             ->where('categoria_id', $request->categoria_id)
    //             ->where('unidade_medida_id', $request->unidade_medida_id)
    //             ->first();

    //         if ($produto) {
    //             // Produto existente: atualiza estoque e cria novo lote
    //             $produto->quantidade_estoque += $request->quantidade_estoque ?? 0;
    //             $produto->estoque_total = $produto->lotes()->sum('quantidade');
    //             $produto->preco_custo = $request->preco_custo;
    //             $produto->preco_venda = $request->preco_venda;
    //             $produto->saveQuietly();

    //             $produto->lotes()->create([
    //                 'produto_id' => $produto->id,
    //                 'descricao' => $produto->descricao,
    //                 'fornecedor_id' => $request->fornecedor_id,
    //                 'quantidade' => $request->quantidade_estoque,
    //                 'preco_compra' => $request->preco_custo,
    //                 'preco_venda' => $request->preco_venda,
    //                 'data_compra' => $request->data_compra,
    //                 'validade_lote' => Carbon::parse($request->validade_produto)->startOfDay(),
    //             ]);

    //         } else {
    //             // Novo produto
    //             $produto = new Produto();
    //             $produto->fill($request->only([
    //                 'fornecedor_id', 'nome', 'sku', 'marca_id',
    //                 'categoria_id', 'descricao', 'unidade_medida_id'
    //             ]));
    //             $produto->quantidade_estoque = $request->quantidade_estoque ?? 0;
    //             $produto->estoque_minimo = $request->estoque_minimo ?? 0;
    //             $produto->ativo = 1;
    //             $produto->preco_custo = $request->preco_custo;
    //             $produto->preco_venda = $request->preco_venda;
    //             $produto->validade_produto = Carbon::parse($request->validade_produto)->startOfDay();

    //             if ($request->hasFile('imagem')) {
    //                 $produto->imagem = $request->file('imagem')->store('produtos', 'public');
    //             }

    //             $produto->save();
    //         }
    //     });

    //     return redirect()->route('produtos.index')
    //                      ->with('success', 'Produto registrado/atualizado com sucesso!');
    // }
   
    public function store(Request $request)
    {
        $this->validateProduto($request);

        DB::transaction(function () use ($request) {

            $produto = Produto::where('nome', $request->nome)
                ->where('sku', $request->sku)
                ->where('marca_id', $request->marca_id)
                ->where('categoria_id', $request->categoria_id)
                ->where('unidade_medida_id', $request->unidade_medida_id)
                ->first();

            if ($produto) {
                // Produto existente: atualiza estoque e cria novo lote
                $produto->quantidade_estoque += $request->quantidade_estoque ?? 0;
                $produto->estoque_total = $produto->lotes()->sum('quantidade');
                $produto->codigo_barras = $request->codigo_barras;
                $produto->descricao = $request->descricao;
                $produto->preco_custo = $request->preco_custo;
                $produto->preco_venda = $request->preco_venda;
                $produto->saveQuietly();

                $produto->lotes()->create([
                    'produto_id' => $produto->id,
                    'descricao' => $produto->descricao,
                    'fornecedor_id' => $request->fornecedor_id,
                    'quantidade' => $request->quantidade_estoque,
                    'preco_compra' => $request->preco_custo,
                    'preco_venda' => $request->preco_venda,
                    'data_compra' => $request->data_compra,
                    'validade_lote' => Carbon::parse($request->validade_produto)->startOfDay(),
                ]);

            } else {
                // Novo produto
                $produto = new Produto();
                $produto->fill($request->only([
                    'fornecedor_id', 'nome','codigo_barras', 'sku', 'marca_id',
                    'categoria_id', 'descricao', 'unidade_medida_id'
                ]));
                $produto->quantidade_estoque = $request->quantidade_estoque ?? 0;
                $produto->estoque_minimo = $request->estoque_minimo ?? 0;
                $produto->ativo = 1;
                $produto->preco_custo = $request->preco_custo;
                $produto->codigo_barras = $request->codigo_barras;
                $produto->preco_venda = $request->preco_venda;
                $produto->validade_produto = Carbon::parse($request->validade_produto)->startOfDay();

                if ($request->hasFile('imagem')) {
                    $produto->imagem = $request->file('imagem')->store('produtos', 'public');
                }

                $produto->save();

                // 🔹 Criar lote para produto novo também
                if (($request->quantidade_estoque ?? 0) > 0) {
                    $produto->lotes()->create([
                        'produto_id' => $produto->id,
                        'descricao' => $produto->descricao,
                        'fornecedor_id' => $request->fornecedor_id,
                        'quantidade' => $request->quantidade_estoque,
                        'preco_compra' => $request->preco_custo,
                        'preco_venda' => $request->preco_venda,
                        'data_compra' => $request->data_compra,
                        'validade_lote' => Carbon::parse($request->validade_produto)->startOfDay(),
                    ]);
                }
            }
        });

        return redirect()->route('produtos.index')
                        ->with('success', 'Produto registrado/atualizado com sucesso!');
    }


    public function indexGrid()
    {
        // CORRETO: retorna LengthAwarePaginator
        $produtos = Produto::where('ativo', 1)->paginate(15);

        return view('produtos.index-grid', compact('produtos'));
    }

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
    /** ATUALIZAR PRODUTO */    
   public function update(Request $request, Produto $produto)
    {
        $this->validateProduto($request, false);

        DB::transaction(function () use ($request, $produto) {

            // Lock para concorrência
            $produto = Produto::lockForUpdate()->find($produto->id);

            // Verifica flag de edição
            if ($produto->editando_por && $produto->editando_por != auth()->id()) {
                $usuario = $produto->usuarioEditando; // relação opcional
                $nomeUsuario = $usuario->name ?? 'Outro usuário';
                throw new \Exception("Este produto está sendo editado por: {$nomeUsuario}");
            }

            // Marca como editando pelo usuário atual
            $produto->editando_por = auth()->id();
            $produto->editando_em = now();
            $produto->save();

            $produto->fill($request->except(['imagem','validade_produto','preco_custo','preco_venda']));

            if ($request->filled('validade_produto')) {
                $validade = Carbon::createFromFormat('Y-m-d', $request->validade_produto)->startOfDay();
                if ($validade->lt(Carbon::today())) {
                    throw new \Exception('A validade não pode ser inferior à data atual.');
                }
                $produto->validade_produto = $validade;
            }

            if ($request->filled('preco_custo') && $request->filled('preco_venda')) {
                if ($request->preco_venda < $request->preco_custo) {
                    throw new \Exception('O preço de venda não pode ser inferior ao preço de custo.');
                }
                $produto->preco_custo = $request->preco_custo;
                $produto->preco_venda = $request->preco_venda;
            }

            if ($request->hasFile('imagem')) {
                if ($produto->imagem) {
                    Storage::disk('public')->delete($produto->imagem);
                }
                $produto->imagem = $request->file('imagem')->store('produtos', 'public');
            }

            // Limpa a flag de edição após salvar
            $produto->editando_por = null;
            $produto->editando_em = null;
            $produto->save();
        });

        return redirect()->route('produtos.index')
                        ->with('success', 'Produto atualizado com sucesso!');
    }

    /** DESATIVAR PRODUTO */
    public function desativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 0;
        $produto->save();

        return redirect()->route('produtos.index')
                         ->with('success', 'Produto desativado com sucesso!');
    }

    /** REATIVAR PRODUTO */
    public function reativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 1;
        $produto->save();

        return redirect()->route('produtos.inativos')
                         ->with('success', 'Produto ativado com sucesso!');
    }

    /** PRODUTOS INATIVOS */
    public function inativos()
    {
        $produtos = Produto::where('ativo', 0)->paginate(15);
        return view('produtos.inativos', compact('produtos'));
    }

    /** PESQUISAR COM VIEWS E CARDS */
    public function search(Request $request)
    {
        $query = $request->input('query');

        $produtos = Produto::with(['categoria','fornecedor','marca','unidadeMedida'])
            ->where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('nome', 'LIKE', "%{$query}%")
                  ->orWhere('codigo_barras', 'LIKE', "%{$query}%")
                  ->orWhere('descricao', 'LIKE', "%{$query}%");
            })
            ->orderBy('nome')
            ->paginate(15);

        return view('produtos.index', compact('produtos'));
    }

    /** PESQUISAR COM VIEWS E grids */
    public function search_grid(Request $request)
    {
        $query = $request->input('query');

        $produtos = Produto::with(['categoria','fornecedor','marca','unidadeMedida'])
            ->where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('nome', 'LIKE', "%{$query}%")
                  ->orWhere('codigo_barras', 'LIKE', "%{$query}%")
                  ->orWhere('descricao', 'LIKE', "%{$query}%");
            })
            ->orderBy('nome')
            ->paginate(15);

        return view('produtos.index-grid', compact('produtos'));
    }

    /** EXIBIR PRODUTO */
    public function show($id)
    {
        $produto = Produto::findOrFail($id);
        return view('produtos.show', compact('produto'));
    }

    /** ATUALIZAR PREÇO BASE */
    public function atualizarPreco(Request $request, $id)
    {
        $request->validate([
            'preco_base' => 'required|numeric|min:0',
        ]);

        $produto = Produto::findOrFail($id);
        $produto->preco_base = $request->preco_base;
        $produto->save();

        return redirect()->back()->with('success', 'Preço base atualizado com sucesso!');
    }

   public function aplicarDesconto(Request $request, $id)
    {
        $request->validate([
            'preco_promocional' => 'nullable|numeric|min:0',
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'promocao_inicio' => 'nullable|date',
            'promocao_fim' => 'nullable|date|after_or_equal:promocao_inicio',
        ]);

        DB::transaction(function () use ($request, $id) {
            $produto = Produto::lockForUpdate()->find($id);

            if ($produto->editando_por && $produto->editando_por != auth()->id()) {
                $usuario = $produto->usuarioEditando;
                $nomeUsuario = $usuario->name ?? 'Outro usuário';
                throw new \Exception("Este produto está sendo editado por: {$nomeUsuario}");
            }

            $produto->editando_por = auth()->id();
            $produto->editando_em = now();

            $produto->preco_promocional = $request->preco_promocional;
            $produto->desconto_percentual = $request->desconto_percentual;
            $produto->promocao_inicio = $request->promocao_inicio;
            $produto->promocao_fim = $request->promocao_fim;

            $hoje = Carbon::today();
            $produto->em_promocao = ($produto->promocao_inicio && $produto->promocao_fim) &&
                                    $hoje->between(Carbon::parse($produto->promocao_inicio), Carbon::parse($produto->promocao_fim));

            $produto->editando_por = null;
            $produto->editando_em = null;
            $produto->save();
        });

        return redirect()->back()->with('success', 'Desconto/promoção aplicado com sucesso!');
    }
    /** RETORNA PREÇO ATUAL (com desconto/promoção) */
    public function precoAtual(Produto $produto)
    {
        $preco = $produto->preco_base;
        $hoje = Carbon::today();

        if ($produto->em_promocao &&
            $produto->promocao_inicio && $produto->promocao_fim &&
            $hoje->between(Carbon::parse($produto->promocao_inicio), Carbon::parse($produto->promocao_fim))) {

            if ($produto->preco_promocional) {
                $preco = $produto->preco_promocional;
            } elseif ($produto->desconto_percentual) {
                $preco = $preco * (1 - $produto->desconto_percentual / 100);
            }
        }

        return $preco;
    }

    /** VALIDAÇÃO */
    protected function validateProduto(Request $request, bool $fullValidation = true)
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'unidade_medida_id' => 'required|exists:unidades_medida,id',
            'marca_id' => 'required|exists:marcas,id',
            'validade_produto' => 'nullable|date',
            'imagem' => 'nullable|image|max:2048',
        ];

        if ($fullValidation) {
            $rules = array_merge($rules, [
                'codigo_barras' => 'nullable|string|max:50',
                'sku' => 'nullable|string|max:50',
                'quantidade_estoque' => 'required|numeric|min:0',
                'estoque_minimo' => 'required|numeric|min:0',
                'preco_custo' => 'required|numeric|min:1',
                'preco_venda' => 'required|numeric|min:1',
                'data_compra' => 'required|date',
                'peso' => 'nullable|numeric|min:0',
                'largura' => 'nullable|numeric|min:0',
                'altura' => 'nullable|numeric|min:0',
                'profundidade' => 'nullable|numeric|min:0',
                'localizacao_estoque' => 'nullable|string|max:255',
            ]);
        }

        $request->validate($rules);
    }

    public function ajustarEstoque(Request $request, Produto $produto)
    {
        $request->validate([
            'tipo' => 'required|in:acrescimo,desconto',
            'quantidade' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request, $produto) {
            $produto = Produto::lockForUpdate()->find($produto->id);

            if ($produto->editando_por && $produto->editando_por != auth()->id()) {
                $usuario = $produto->usuarioEditando;
                $nomeUsuario = $usuario->name ?? 'Outro usuário';
                throw new \Exception("Este produto está sendo editado por: {$nomeUsuario}");
            }

            $produto->editando_por = auth()->id();
            $produto->editando_em = now();

            if ($request->tipo === 'acrescimo') {
                $produto->quantidade_estoque += $request->quantidade;
            } else {
                $produto->quantidade_estoque -= $request->quantidade;
                if ($produto->quantidade_estoque < 0) $produto->quantidade_estoque = 0;
            }

            $produto->editando_por = null;
            $produto->editando_em = null;
            $produto->save();
        });

        return back()->with('success', 'Estoque atualizado com sucesso!');
    }

    public function ajustarPreco(Request $request, Produto $produto)
    {
        $request->validate([
            'tipo' => 'required|in:acrescimo,desconto',
            'valor' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $produto) {
            $produto = Produto::lockForUpdate()->find($produto->id);

            if ($produto->editando_por && $produto->editando_por != auth()->id()) {
                $usuario = $produto->usuarioEditando;
                $nomeUsuario = $usuario->name ?? 'Outro usuário';
                throw new \Exception("Este produto está sendo editado por: {$nomeUsuario}");
            }

            $produto->editando_por = auth()->id();
            $produto->editando_em = now();

            if ($request->tipo === 'acrescimo') {
                $produto->preco_venda += $produto->preco_venda * ($request->valor / 100);
            } else {
                $produto->preco_venda -= $produto->preco_venda * ($request->valor / 100);
            }

            $produto->editando_por = null;
            $produto->editando_em = null;
            $produto->save();
        });

        return back()->with('success', 'Preço atualizado com sucesso!');
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
