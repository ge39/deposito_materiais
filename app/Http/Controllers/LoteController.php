<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Produto;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    /**
     * Lista todos os lotes ou os lotes de um produto específico
     */
    public function index(Request $request, $produto_id = null)
    {
        $search = $request->input('search');

        if ($produto_id) {
            $produto = Produto::findOrFail($produto_id);

            $lotes = $produto->lotes()
                ->when($search, function ($query) use ($search) {
                    $query->where('codigo_lote', 'LIKE', "%$search%");
                })
                ->orderBy('id', 'DESC')
                ->paginate(20);

            return view('lotes.index', compact('produto', 'lotes', 'search'));
        }

        $lotes = Lote::with('produto')
            ->when($search, function ($query) use ($search) {
                $query->where('codigo_lote', 'LIKE', "%$search%")
                      ->orWhereHas('produto', function ($q) use ($search) {
                          $q->where('nome', 'LIKE', "%$search%");
                      });
            })
            ->orderBy('id', 'DESC')
            ->paginate(20);

        return view('lotes.index', compact('lotes', 'search'));
    }

    /**
     * Formulário de criação
     */
    public function create($produto_id = null)
    {
        $produtos = Produto::orderBy('nome')->get();
        $produto = $produto_id ? Produto::find($produto_id) : null;

        return view('lotes.create', compact('produtos', 'produto'));
    }

    /**
     * Salvar novo lote
     */
    public function store(Request $request)
    {
        $request->validate([
            'produto_id'     => 'required|exists:produtos,id',
            'codigo_lote'    => 'required|string|max:100|unique:lotes,codigo_lote',
            'quantidade'     => 'required|numeric|min:0',
            'validade_lote'  => 'required|date',
        ]);

        Lote::create([
            'produto_id'    => $request->produto_id,
            'codigo_lote'   => $request->codigo_lote,
            'quantidade'    => $request->quantidade,
            'validade_lote' => $request->validade_lote,
        ]);

        return redirect()->route('lotes.index')
            ->with('success', 'Lote cadastrado com sucesso.');
    }

    /**
     * Exibir dados de um lote
     */
    public function show($id)
    {
        $lote = Lote::with('produto')->findOrFail($id);
        return view('lotes.show', compact('lote'));
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $lote = Lote::findOrFail($id);
        $produtos = Produto::orderBy('nome')->get();

        return view('lotes.edit', compact('lote', 'produtos'));
    }

    /**
     * Atualizar lote
     */
    public function update(Request $request, $id)
    {
        $lote = Lote::findOrFail($id);

        $request->validate([
            'produto_id'     => 'required|exists:produtos,id',
            'codigo_lote'    => "required|string|max:100|unique:lotes,codigo_lote,{$lote->id}",
            'quantidade'     => 'required|numeric|min:0',
            'validade_lote'  => 'required|date',
        ]);

        $lote->update([
            'produto_id'    => $request->produto_id,
            'codigo_lote'   => $request->codigo_lote,
            'quantidade'    => $request->quantidade,
            'validade_lote' => $request->validade_lote,
        ]);

        return redirect()->route('lotes.index')
            ->with('success', 'Lote atualizado com sucesso.');
    }

    /**
     * Excluir lote
     */
    public function destroy($id)
    {
        $lote = Lote::findOrFail($id);
        $lote->delete();

        return redirect()->route('lotes.index')
            ->with('success', 'Lote removido com sucesso.');
    }
}
