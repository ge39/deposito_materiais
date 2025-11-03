<?php

namespace App\Http\Controllers;

use App\Models\Promocao;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class PromocaoController extends Controller
{
    /**
     * Construtor: aplica middleware de autenticação e autorização.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'can:gerenciar-promocoes']);
    }

    /**
     * Exibe a lista de promoções.
     */
    public function index()
    {
        $promocoes = Promocao::with(['produto', 'categoria'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('promocoes.index', compact('promocoes'));
    }

    /**
     * Mostra o formulário de criação de promoção.
     */
    public function create()
    {
        $produtos = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('promocoes.create', compact('produtos', 'categorias'));
    }

    /**
     * Armazena uma nova promoção.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_abrangencia' => 'required|in:produto,categoria,geral',
            'produto_id' => 'nullable|exists:produtos,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'acrescimo_percentual' => 'nullable|numeric|min:0|max:100',
            'acrescimo_valor' => 'nullable|numeric|min:0',
            'preco_promocional' => 'nullable|numeric|min:0',
            'promocao_inicio' => 'nullable|date',
            'promocao_fim' => 'nullable|date|after_or_equal:promocao_inicio',
            'em_promocao' => 'nullable|boolean',
        ]);

        $promocao = Promocao::create($validated);

        $this->aplicarPromocao($promocao);

        return redirect()->route('promocoes.index')->with('success', 'Promoção criada com sucesso!');
    }

    /**
     * Exibe detalhes da promoção.
     */
    public function show(Promocao $promocao)
    {
        return view('promocoes.show', compact('promocao'));
    }

    /**
     * Mostra formulário de edição.
     */
    public function edit(Promocao $promocao)
    {
        $produtos = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('promocoes.edit', compact('promocao', 'produtos', 'categorias'));
    }

    /**
     * Atualiza a promoção.
     */
    public function update(Request $request, Promocao $promocao)
    {
        $validated = $request->validate([
            'tipo_abrangencia' => 'required|in:produto,categoria,geral',
            'produto_id' => 'nullable|exists:produtos,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'acrescimo_percentual' => 'nullable|numeric|min:0|max:100',
            'acrescimo_valor' => 'nullable|numeric|min:0',
            'preco_promocional' => 'nullable|numeric|min:0',
            'promocao_inicio' => 'nullable|date',
            'promocao_fim' => 'nullable|date|after_or_equal:promocao_inicio',
            'em_promocao' => 'nullable|boolean',
        ]);

        $promocao->update($validated);

        $this->aplicarPromocao($promocao);

        return redirect()->route('promocoes.index')->with('success', 'Promoção atualizada com sucesso!');
    }

    /**
     * Remove uma promoção.
     */
    public function destroy(Promocao $promocao)
    {
        $promocao->delete();
        return redirect()->route('promocoes.index')->with('success', 'Promoção excluída com sucesso!');
    }

    /**
     * Ativa ou desativa rapidamente uma promoção.
     */
    public function toggleStatus(Promocao $promocao)
    {
        $promocao->em_promocao = !$promocao->em_promocao;
        $promocao->save();

        if ($promocao->em_promocao) {
            $this->aplicarPromocao($promocao);
        }

        return redirect()->back()->with('success', 'Status da promoção atualizado!');
    }

    /**
     * Aplica a promoção nos produtos afetados.
     */
    private function aplicarPromocao(Promocao $promocao)
    {
        if (!$promocao->em_promocao) {
            return;
        }

        if ($promocao->tipo_abrangencia === 'produto' && $promocao->produto_id) {
            $produtos = Produto::where('id', $promocao->produto_id)->get();
        } elseif ($promocao->tipo_abrangencia === 'categoria' && $promocao->categoria_id) {
            $produtos = Produto::where('categoria_id', $promocao->categoria_id)->get();
        } else {
            $produtos = Produto::all();
        }

        foreach ($produtos as $produto) {
            $precoBase = $produto->preco_base ?? $produto->preco;

            $precoFinal = $precoBase;

            if ($promocao->desconto_percentual > 0) {
                $precoFinal -= ($precoBase * ($promocao->desconto_percentual / 100));
            }

            if ($promocao->acrescimo_percentual > 0) {
                $precoFinal += ($precoBase * ($promocao->acrescimo_percentual / 100));
            }

            if ($promocao->acrescimo_valor > 0) {
                $precoFinal += $promocao->acrescimo_valor;
            }

            if ($promocao->preco_promocional > 0) {
                $precoFinal = $promocao->preco_promocional;
            }

            $produto->update([
                'preco' => $precoFinal
            ]);
        }
    }
}
