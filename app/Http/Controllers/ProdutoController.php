<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use App\Models\Marca;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    // Listar produtos ativos
    public function index()
    {
        $produtos = Produto::with(['categoria', 'fornecedor', 'marca', 'unidadeMedida'])
            ->where('ativo', 1)
            ->paginate(15);

        return view('produtos.index', compact('produtos'));
    }

    // Buscar produtos
    public function search(Request $request)
    {
        $q = $request->input('q');

        $produtos = Produto::query()
            ->where('nome', 'like', "%{$q}%")
            ->orWhereHas('categoria', fn($c) => $c->where('nome', 'like', "%{$q}%"))
            ->orWhereHas('fornecedor', fn($f) => $f->where('nome', 'like', "%{$q}%"))
            ->paginate(15)
            ->withQueryString();

        return view('produtos.index', compact('produtos'));
    }

    // Formulário de criação
    public function create()
    {
        $categorias = Categoria::all();
        $fornecedores = Fornecedor::all();
        $unidades = UnidadeMedida::all();
        $marcas = Marca::all();

        return view('produtos.create', compact('categorias', 'fornecedores', 'unidades', 'marcas'));
    }

    // Salvar novo produto
    public function store(Request $request)
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
            'data_compra' => 'required|date',
            'validade' => 'nullable|date',
            'preco_venda' => 'required|numeric|min:0',
            'peso' => 'nullable|numeric|min:0',
            'largura' => 'nullable|numeric|min:0',
            'altura' => 'nullable|numeric|min:0',
            'profundidade' => 'nullable|numeric|min:0',
            'localizacao_estoque' => 'nullable|string|max:255',
            'imagem' => 'nullable|image|max:2048',
        ]);

        $produto = new Produto($request->only([
            'nome','codigo_barras','sku','descricao','categoria_id','fornecedor_id',
            'unidade_medida_id','marca_id','quantidade_estoque','estoque_minimo',
            'preco_custo','data_compra','validade','preco_venda','peso','largura',
            'altura','profundidade','localizacao_estoque'
        ]));

        // Upload de imagem
        if ($request->hasFile('imagem')) {
            $produto->imagem = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->ativo = 1;
        $produto->save();

        // Criar lote automático
        if ($produto->quantidade_estoque > 0) {
            \App\Models\Lote::create([
                'produto_id'   => $produto->id,
                'fornecedor_id'=> $produto->fornecedor_id,
                'quantidade'   => $produto->quantidade_estoque,
                'preco_compra' => $produto->preco_custo,
                'data_compra'  => $produto->data_compra,
                'validade'     => $produto->validade,
                'numero_lote'  => date('Ymd') . $produto->id,
            ]);
        }

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    // Exibir produto
    public function show(Produto $produto)
    {
        return view('produtos.show', compact('produto'));
    }

    // Formulário de edição
    public function edit(Produto $produto)
    {
        $categorias = Categoria::all();
        $fornecedores = Fornecedor::all();
        $unidades = UnidadeMedida::all();
        $marcas = Marca::all();

        return view('produtos.edit', compact('produto', 'categorias', 'fornecedores', 'unidades', 'marcas'));
    }

    // Atualizar produto
    public function update(Request $request, Produto $produto)
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
            'data_compra' => 'required|date',
            'validade' => 'nullable|date',
            'preco_venda' => 'required|numeric|min:0',
            'peso' => 'nullable|numeric|min:0',
            'largura' => 'nullable|numeric|min:0',
            'altura' => 'nullable|numeric|min:0',
            'profundidade' => 'nullable|numeric|min:0',
            'localizacao_estoque' => 'nullable|string|max:255',
            'imagem' => 'nullable|image|max:2048',
        ]);

        $produto->fill($request->only([
            'nome','codigo_barras','sku','descricao','categoria_id','fornecedor_id',
            'unidade_medida_id','marca_id','quantidade_estoque','estoque_minimo',
            'preco_custo','data_compra','validade','preco_venda','peso','largura',
            'altura','profundidade','localizacao_estoque'
        ]));

        // Upload de imagem
        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $produto->imagem = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->save();

        // Lógica de lote
        if ($produto->quantidade_estoque > 0) {
            // Criar novo lote se quantidade positiva
            \App\Models\Lote::create([
                'produto_id'   => $produto->id,
                'fornecedor_id'=> $produto->fornecedor_id,
                'quantidade'   => $produto->quantidade_estoque,
                'preco_compra' => $produto->preco_custo,
                'data_compra'  => $produto->data_compra,
                'validade'     => $produto->validade,
                'numero_lote'  => date('Ymd') . $produto->id,
            ]);
        } else {
            // Atualiza lote existente se estoque <= 0
            \App\Models\Lote::updateOrCreate(
                [
                    'produto_id'  => $produto->id,
                    'numero_lote' => 'SEM_LOTE',
                ],
                [
                    'quantidade'   => $produto->quantidade_estoque,
                    'fornecedor_id'=> $produto->fornecedor_id,
                    'preco_compra' => $produto->preco_custo,
                    'data_compra'  => $produto->data_compra,
                    'validade'     => $produto->validade,
                ]
            );
        }

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Listar produtos inativos
    public function inativos()
    {
        $produtos = Produto::where('ativo', 0)->paginate(10);
        return view('produtos.inativos', compact('produtos'));
    }

    // Desativar produto
    public function desativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 0;
        $produto->save();

        return redirect()->route('produtos.index')->with('success', 'Produto desativado com sucesso!');
    }

    // Ativar produto
    public function reativar($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->ativo = 1;
        $produto->save();

        return redirect()->route('produtos.inativos')->with('success', 'Produto ativado com sucesso!');
    }
}
