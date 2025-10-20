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

        $produto = new Produto();
        $produto->nome = $request->nome;
        $produto->codigo_barras = $request->codigo_barras;
        $produto->sku = $request->sku;
        $produto->descricao = $request->descricao;
        $produto->categoria_id = $request->categoria_id;
        $produto->fornecedor_id = $request->fornecedor_id;
        $produto->unidade_medida_id = $request->unidade_medida_id;
        $produto->marca_id = $request->marca_id;
        $produto->quantidade_estoque = $request->quantidade_estoque;
        $produto->estoque_minimo = $request->estoque_minimo;
        $produto->preco_custo = $request->preco_custo;
        $produto->data_compra = Carbon::parse($request->data_compra);
        $produto->validade = $request->validade ? Carbon::parse($request->validade) : null;
        $produto->preco_venda = $request->preco_venda;
        $produto->peso = $request->peso;
        $produto->largura = $request->largura;
        $produto->altura = $request->altura;
        $produto->profundidade = $request->profundidade;
        $produto->localizacao_estoque = $request->localizacao_estoque;

        // Upload de imagem
        if ($request->hasFile('imagem')) {
            $produto->imagem = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->ativo = 1;
        $produto->save(); // **Observer cria o lote automaticamente aqui**

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

        $produto->nome = $request->nome;
        $produto->codigo_barras = $request->codigo_barras;
        $produto->sku = $request->sku;
        $produto->descricao = $request->descricao;
        $produto->categoria_id = $request->categoria_id;
        $produto->fornecedor_id = $request->fornecedor_id;
        $produto->unidade_medida_id = $request->unidade_medida_id;
        $produto->marca_id = $request->marca_id;
        $produto->quantidade_estoque = $request->quantidade_estoque;
        $produto->estoque_minimo = $request->estoque_minimo;
        $produto->preco_custo = $request->preco_custo;
        $produto->data_compra = Carbon::parse($request->data_compra);
        $produto->validade = $request->validade ? Carbon::parse($request->validade) : null;
        $produto->preco_venda = $request->preco_venda;
        $produto->peso = $request->peso;
        $produto->largura = $request->largura;
        $produto->altura = $request->altura;
        $produto->profundidade = $request->profundidade;
        $produto->localizacao_estoque = $request->localizacao_estoque;

        // Upload de imagem
        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $produto->imagem = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->save();

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // ProdutoController.php

    public function inativos()
    {
        // Pega todos os produtos com ativo = 0
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
