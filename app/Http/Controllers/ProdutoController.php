<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use App\Models\Marca;
use App\Models\Lote;
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

    /** FORMULÁRIO DE CRIAÇÃO */
    public function create()
    {
        return view('produtos.create', [
            'categorias' => Categoria::all(),
            'fornecedores' => Fornecedor::all(),
            'unidades' => UnidadeMedida::all(),
            'marcas' => Marca::all(),
            'produtosExistentes' => Produto::all(),
        ]);
    }

    /** SALVAR NOVO PRODUTO */
    public function store(Request $request)
    {
        $this->validateProduto($request);

        DB::transaction(function () use ($request) {

            $produto = Produto::where('nome', $request->nome)
                ->where('marca_id', $request->marca_id)
                ->where('fornecedor_id', $request->fornecedor_id)
                ->first();

            if ($produto) {
                // Produto existente → atualiza estoque e preços
                $produto->quantidade_estoque += $request->quantidade_estoque;
                $produto->preco_custo = max($request->preco_custo, 1.00);
                $produto->preco_venda = max($request->preco_venda, 1.00);
                $produto->data_compra = $request->data_compra;
                $produto->validade_produto = $request->validade_produto ?: null;

                if ($request->hasFile('imagem')) {
                    if ($produto->imagem) {
                        Storage::disk('public')->delete($produto->imagem);
                    }
                    $produto->imagem = $request->file('imagem')->store('produtos', 'public');
                }

                $produto->save();

            } else {
                // Novo produto
                $produto = new Produto();
                $produto->fill($request->except(['imagem']));
                $produto->ativo = 1;

                if ($request->hasFile('imagem')) {
                    $produto->imagem = $request->file('imagem')->store('produtos', 'public');
                }

                $produto->save();
            }

            // Atualizar estoque_total (substitui triggers antigas)
            $produto->estoque_total = $produto->lotes()->sum('quantidade');
            $produto->saveQuietly();
        });

        return redirect()->route('produtos.index')
                         ->with('success', 'Produto registrado com sucesso!');
    }

    /** FORMULÁRIO DE EDIÇÃO */
    public function edit(Produto $produto)
    {
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
            $produto->fill($request->except([
                'quantidade_estoque',
                'estoque_minimo',
                'preco_custo',
                'preco_venda',
                'data_compra',
                'imagem',
            ]));

            if ($request->hasFile('imagem')) {
                if ($produto->imagem) {
                    Storage::disk('public')->delete($produto->imagem);
                }
                $produto->imagem = $request->file('imagem')->store('produtos', 'public');
            }

            $produto->save();

            // Atualiza estoque_total (sem trigger)
            $produto->estoque_total = $produto->lotes()->sum('quantidade');
            $produto->saveQuietly();
        });

        return redirect()->route('produtos.index')
                         ->with('success', 'Produto atualizado com sucesso!');
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

    /** PESQUISAR */
    public function search(Request $request)
    {
        $query = $request->input('query');

        $produtos = Produto::with(['categoria', 'fornecedor', 'marca', 'unidadeMedida'])
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

    /** EXIBIR PRODUTO */
    public function show($id)
    {
        $produto = Produto::findOrFail($id);
        return view('produtos.show', compact('produto'));
    }
}
