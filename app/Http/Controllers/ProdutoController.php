<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    /** LISTAR PRODUTOS ATIVOS */
    public function index()
    {
        $produtos = Produto::with(['categoria', 'fornecedor', 'marca', 'unidadeMedida'])
            ->where('ativo', 1)
            ->paginate(15);

        return view('produtos.index', compact('produtos'));
    }

    /** BUSCAR PRODUTOS (AJAX com debounce) */
    public function search(Request $request)
    {
        $q = $request->input('q');

        $produtos = Produto::query()
            ->where('nome', 'like', "%{$q}%")
            ->orWhereHas('categoria', fn($c) => $c->where('nome', 'like', "%{$q}%"))
            ->orWhereHas('fornecedor', fn($f) => $f->where('nome', 'like', "%{$q}%"))
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('produtos.partials.table', compact('produtos'))->render();
        }

        return view('produtos.index', compact('produtos'));
    }

    /** FORMULÁRIO DE CRIAÇÃO */
    public function create()
    {
        $categorias = Categoria::all();
        $fornecedores = Fornecedor::all();
        $unidades = UnidadeMedida::all();
        $marcas = Marca::all();
        
        //deve enviar $produtosExistentes para a view:
        $produtosExistentes = Produto::orderBy('nome')->get();
        return view('produtos.create', compact('categorias', 'fornecedores', 'unidades', 'marcas', 'produtosExistentes'));

        return view('produtos.create', compact('categorias', 'fornecedores', 'unidades', 'marcas'));
    }

    /** SALVAR NOVO PRODUTO */
    public function store(Request $request)
    {
        $this->validateProduto($request);

        DB::transaction(function () use ($request, &$produto) {
            $produto = new Produto($request->except('imagem'));
            $produto->ativo = 1;

            if ($request->hasFile('imagem')) {
                $produto->imagem = $request->file('imagem')->store('produtos', 'public');
            }

            $produto->save();
        });

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    /** EXIBIR PRODUTO */
    public function show(Produto $produto)
    {
        return view('produtos.show', compact('produto'));
    }

    /** FORMULÁRIO DE EDIÇÃO */
    public function edit(Produto $produto)
    {
        $categorias = Categoria::all();
        $fornecedores = Fornecedor::all();
        $unidades = UnidadeMedida::all();
        $marcas = Marca::all();

        return view('produtos.edit', compact('produto', 'categorias', 'fornecedores', 'unidades', 'marcas'));
    }

    /** ATUALIZAR PRODUTO */
    public function update(Request $request, Produto $produto)
    {
        $this->validateProduto($request);

        DB::transaction(function () use ($request, $produto) {
            $produto->fill($request->except('imagem'));

            if ($request->hasFile('imagem')) {
                if ($produto->imagem) {
                    Storage::disk('public')->delete($produto->imagem);
                }
                $produto->imagem = $request->file('imagem')->store('produtos', 'public');
            }

            $produto->save();
        });

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    /** LISTAR PRODUTOS INATIVOS */
    public function inativos()
    {
        $produtos = Produto::where('ativo', 0)->paginate(10);
        return view('produtos.inativos', compact('produtos'));
    }

    /** DESATIVAR PRODUTO */
    public function desativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 0;
        $produto->save();

        return redirect()->route('produtos.index')->with('success', 'Produto desativado com sucesso!');
    }

    /** ATIVAR PRODUTO */
    public function reativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 1;
        $produto->save();

        return redirect()->route('produtos.inativos')->with('success', 'Produto ativado com sucesso!');
    }
    
    /** VALIDAÇÃO DE PRODUTO */
    protected function validateProduto(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo_barras' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:50',
            'descricao' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'fornecedor_id' => 'required|exists:fornecedores,id',
            'unidade_medida_id' => 'required|exists:unidades_medida,id',
            'marca_id' => 'required|exists:marcas,id',
            'quantidade_estoque' => 'required|numeric|min:0',
            'estoque_minimo' => 'required|numeric|min:0',
            'preco_custo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0',
            'data_compra' => 'required|date',
            'validade' => 'nullable|date',
            'peso' => 'nullable|numeric|min:0',
            'largura' => 'nullable|numeric|min:0',
            'altura' => 'nullable|numeric|min:0',
            'profundidade' => 'nullable|numeric|min:0',
            'localizacao_estoque' => 'nullable|string|max:255',
            'imagem' => 'nullable|image|max:2048',
        ]);
    }
}
