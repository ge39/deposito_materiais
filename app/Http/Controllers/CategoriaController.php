<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Produto;

class CategoriaController extends Controller
{
    // Listar todas as categorias
    public function index()
    {
        $categorias = Categoria::all();
        return view('categorias.index', compact('categorias'));
    }

    // Criar nova categoria
    public function create()
    {
        return view('categorias.create');
    }

    // Salvar nova categoria
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Categoria::create($request->all());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    // Editar categoria
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    // Atualizar categoria
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $categoria->update($request->all());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    // Excluir categoria
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }

    // ==========================
    // MÉTODO PARA PREÇO MÉDIO
    // ==========================
    public function precoMedio($id)
    {
        $categoria = Categoria::findOrFail($id);

        // Calcula a média de preços dos produtos ativos desta categoria
        $precoMedio = Produto::where('categoria_id', $categoria->id)
            ->where('ativo', 1) // opcional, se tiver campo ativo
            ->avg('preco');

        return response()->json([
            'preco_medio' => $precoMedio ? round($precoMedio, 2) : 0
        ]);
    }
}
