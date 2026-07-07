<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use App\Models\Marca;
use App\Models\LocalizacaoEstoque;
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
        $produtos = Produto::with([
            'categoria',
            'fornecedor',
            'marca',
            'unidadeMedida',
            'localizacaoEstoque',
            'lotes'
        ])
        ->where('ativo', 1)
        ->paginate(15);

        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        $categorias = Categoria::where('ativo', 1)->orderBy('nome')->get();
        $fornecedores = Fornecedor::where('ativo', 1)->orderBy('nome')->get();
        $unidades = UnidadeMedida::where('ativo', 1)->orderBy('nome')->get();
        $marcas = Marca::where('ativo', 1)->orderBy('nome')->get();

        $localizacoesEstoque = LocalizacaoEstoque::where('ativo', 1)
            ->orderBy('ordem_coleta')
            ->orderBy('codigo')
            ->get();

        $unidadesMedida = UnidadeMedida::where('ativo', 1)->orderBy('nome')->get();

        $produtosExistentes = Produto::with([
            'lotes' => function ($query) {
                $query->where('status', 'ativo')
                    ->where('quantidade', '>', 0);
            }
        ])
        ->orderBy('nome')
        ->get()
        ->map(function ($produto) {
            $produto->quantidade_disponivel = $produto->lotes->sum('quantidade');
            return $produto;
        });

        return view('produtos.create', compact(
            'categorias',
            'fornecedores',
            'unidades',
            'marcas',
            'produtosExistentes',
            'unidadesMedida',
            'localizacoesEstoque'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'                  => 'required|string|max:255',
            'sku'                   => 'nullable|string|max:50|unique:produtos,sku',
            'codigo_barras'         => 'nullable|string|max:50|unique:produtos,codigo_barras',
            'categoria_id'          => 'required|exists:categorias,id',
            'fornecedor_id'         => 'required|exists:fornecedores,id',
            'unidade_medida_id'     => 'nullable|exists:unidades_medida,id',
            'marca_id'              => 'nullable|exists:marcas,id',
            'preco_venda'           => 'required|numeric|min:0',
            'preco_compra_atual'    => 'nullable|numeric|min:0',
            'estoque_minimo'        => 'nullable|integer|min:0',
            'quantidade_estoque'    => 'nullable|integer',
            'peso'                  => 'nullable|numeric|min:0',
            'largura'               => 'nullable|numeric|min:0',
            'altura'                => 'nullable|numeric|min:0',
            'profundidade'          => 'nullable|numeric|min:0',
            'localizacao_estoque_id'=> 'nullable|exists:localizacoes_estoque,id',
            'descricao'             => 'nullable|string',
            'ncm'                   => 'nullable|string|max:8',
            'cest'                  => 'nullable|string|max:7',
            'cfop'                  => 'nullable|string|max:4',
            'icms_csosn'            => 'nullable|string|max:4',
            'origem'                => 'nullable|integer',
            'ativo'                 => 'nullable|boolean',
            'em_promocao'           => 'nullable|boolean',
            'controla_validade'     => 'nullable|boolean',
            'validade_produto'      => 'nullable|date',
            'markup_1'              => 'nullable|numeric|min:0',
            'markup_2'              => 'nullable|numeric|min:0',
            'markup_3'              => 'nullable|numeric|min:0',
            'preco_venda_2'         => 'nullable|numeric|min:0',
            'preco_venda_3'         => 'nullable|numeric|min:0',
            'imagem'                => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        \DB::beginTransaction();

        try {
            $produto = new Produto($validated);

            $produto->ativo = $request->has('ativo') ? 1 : 0;
            $produto->em_promocao = $request->has('em_promocao') ? 1 : 0;
            $produto->controla_validade = $request->has('controla_validade') ? 1 : 0;

            $produto->save();

            if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                $destino = public_path('image/produtos');

                if (!file_exists($destino)) {
                    mkdir($destino, 0755, true);
                }

                $arquivo = $request->file('imagem');
                $nomeLimpo = preg_replace('/[^A-Za-z0-9._-]/', '_', $arquivo->getClientOriginalName());
                $nomeLimpo = preg_replace('/_+/', '_', $nomeLimpo);

                $nomeArquivo = time() . '_' . $nomeLimpo;
                $arquivo->move($destino, $nomeArquivo);

                $produto->imagem = 'image/produtos/' . $nomeArquivo;
                $produto->save();
            }

            \DB::commit();

            return redirect()->route('produtos.index')
                ->with('success', 'Produto cadastrado com sucesso!');
        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao salvar o produto: ' . $e->getMessage());
        }
    }

    public function edit(Produto $produto)
    {
        if ($produto->editando_por && $produto->editando_por != auth()->id()) {
            $usuario = \App\Models\User::find($produto->editando_por);
            $nome = $usuario ? $usuario->name : 'Outro usuário';

            return redirect()->route('produtos.index')
                ->with('error', "Este produto já está sendo editado por $nome.");
        }

        $produto->editando_por = auth()->id();
        $produto->editando_em = now();
        $produto->save();

        $produto->load([
            'localizacaoEstoque',
            'lotes' => function ($query) {
                $query->latest();
            }
        ]);

        return view('produtos.edit', [
            'produto' => $produto,
            'categorias' => Categoria::all(),
            'fornecedores' => Fornecedor::all(),
            'unidades' => UnidadeMedida::all(),
            'marcas' => Marca::all(),
            'localizacoesEstoque' => LocalizacaoEstoque::where('ativo', 1)
                ->orderBy('ordem_coleta')
                ->orderBy('codigo')
                ->get(),
        ]);
    }

    public function update(Request $request, Produto $produto)
    {
        $this->validateProduto($request, false);

        if ($produto->editando_por && $produto->editando_por != auth()->id()) {
            $usuario = $produto->usuarioEditando;
            $nomeUsuario = $usuario->name ?? 'Outro usuário';

            return redirect()
                ->route('produtos.index')
                ->with('error', "Este produto está sendo editado por: {$nomeUsuario}");
        }

        $data = $request->except(['imagem']);

        $data['ativo'] = $request->has('ativo') ? true : false;
        $data['em_promocao'] = $request->has('em_promocao') ? true : false;
        $data['controla_validade'] = $request->has('controla_validade') ? true : false;

        if ($data['controla_validade'] === true) {
            $request->validate([
                'validade_produto' => 'required|date|after_or_equal:today',
            ]);
        } else {
            $data['validade_produto'] = null;
        }

        DB::transaction(function () use ($request, $produto, $data) {
            $produto->fill($data);

            if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                if ($produto->imagem) {
                    $imagemAntiga = public_path($produto->imagem);

                    if (file_exists($imagemAntiga)) {
                        unlink($imagemAntiga);
                    }
                }

                $arquivo = $request->file('imagem');
                $nomeArquivo = time() . '_' . preg_replace(
                    '/[^A-Za-z0-9\.\-_]/',
                    '_',
                    $arquivo->getClientOriginalName()
                );

                $destino = public_path('image/produtos');

                if (!file_exists($destino)) {
                    mkdir($destino, 0755, true);
                }

                $arquivo->move($destino, $nomeArquivo);

                $produto->imagem = 'image/produtos/' . $nomeArquivo;
            }

            if ($produto->editando_por == auth()->id()) {
                $produto->editando_por = null;
                $produto->editando_em = null;
            }

            $produto->save();
        });

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $produtos = Produto::with([
                'categoria',
                'fornecedor',
                'marca',
                'unidadeMedida',
                'localizacaoEstoque'
            ])
            ->where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nome', 'LIKE', "%$query%")
                        ->orWhere('codigo_barras', 'LIKE', "%$query%")
                        ->orWhere('descricao', 'LIKE', "%$query%")
                        ->orWhere('id', $query);
                })
                ->orWhereHas('categoria', fn($c) =>
                    $c->where('ativo',1)->where('nome','LIKE',"%$query%")
                )
                ->orWhereHas('fornecedor', fn($f) =>
                    $f->where('ativo',1)->where('nome','LIKE',"%$query%")
                )
                ->orWhereHas('marca', fn($m) =>
                    $m->where('ativo',1)->where('nome','LIKE',"%$query%")
                );
            })
            ->addSelect([
                'estoque_total' => DB::table('lotes')
                    ->selectRaw('COALESCE(SUM(quantidade),0)')
                    ->whereColumn('produto_id', 'produtos.id')
                    ->where('status', 1)
            ])
            ->addSelect([
                'quantidade_reservada' => DB::table('lotes')
                    ->selectRaw('COALESCE(SUM(quantidade_reservada),0)')
                    ->whereColumn('produto_id', 'produtos.id')
                    ->where('status', 1)
            ])
            ->addSelect([
                'disponivel' => DB::table('lotes')
                    ->selectRaw('
                        COALESCE(SUM(quantidade),0)
                        - COALESCE(SUM(quantidade_reservada),0)
                    ')
                    ->whereColumn('produto_id', 'produtos.id')
                    ->where('status', 1)
            ])
            ->paginate(20);

        return view('produtos.index', compact('produtos'));
    }

    public function search_grid(Request $request)
    {
        $query = $request->input('query');

        $produtos = Produto::with([
                'categoria',
                'fornecedor',
                'marca',
                'unidadeMedida',
                'localizacaoEstoque'
            ])
            ->where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nome', 'LIKE', "%$query%")
                        ->orWhere('codigo_barras', 'LIKE', "%$query%")
                        ->orWhere('descricao', 'LIKE', "%$query%")
                        ->orWhere('id', $query);
                })
                ->orWhereHas('categoria', function ($cat) use ($query) {
                    $cat->where('ativo', 1)
                        ->where('nome', 'LIKE', "%$query%");
                })
                ->orWhereHas('fornecedor', function ($for) use ($query) {
                    $for->where('ativo', 1)
                        ->where('nome', 'LIKE', "%$query%");
                })
                ->orWhereHas('marca', function ($mar) use ($query) {
                    $mar->where('ativo', 1)
                        ->where('nome', 'LIKE', "%$query%");
                });
            })
            ->addSelect([
                'estoque_total' => DB::table('lotes')
                    ->selectRaw('COALESCE(SUM(quantidade),0)')
                    ->whereColumn('produto_id', 'produtos.id')
                    ->where('status', 1)
            ])
            ->addSelect([
                'quantidade_reservada' => DB::table('lotes')
                    ->selectRaw('COALESCE(SUM(quantidade_reservada),0)')
                    ->whereColumn('produto_id', 'produtos.id')
                    ->where('status', 1)
            ])
            ->addSelect([
                'disponivel' => DB::table('lotes')
                    ->selectRaw('
                        COALESCE(SUM(quantidade),0)
                        - COALESCE(SUM(quantidade_reservada),0)
                    ')
                    ->whereColumn('produto_id', 'produtos.id')
                    ->where('status', 1)
            ])
            ->paginate(20);

        return view('produtos.index', compact('produtos'));
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
            'localizacao_estoque_id' => 'nullable|exists:localizacoes_estoque,id',
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
        $produtos = Produto::with([
                'categoria',
                'fornecedor',
                'marca',
                'unidadeMedida',
                'localizacaoEstoque'
            ])
            ->where('ativo', 1)
            ->paginate(15);

        return view('produtos.index-grid', compact('produtos'));
    }

    public function show($id)
    {
        $produto = Produto::with([
                'categoria',
                'fornecedor',
                'marca',
                'unidadeMedida',
                'localizacaoEstoque',
                'lotes'
            ])
            ->findOrFail($id);

        return view('produtos.show', compact('produto'));
    }

    public function limparEdicao($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->editando_por = null;
        $produto->editando_em = null;
        $produto->save();

        return response()->json(['status' => 'ok']);
    }
}