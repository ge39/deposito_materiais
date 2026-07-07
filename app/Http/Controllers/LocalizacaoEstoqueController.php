<?php

namespace App\Http\Controllers;

use App\Models\LocalizacaoEstoque;
use Illuminate\Http\Request;

class LocalizacaoEstoqueController extends Controller
{
    public function index(Request $request)
    {
        $localizacoes = LocalizacaoEstoque::query()
            ->withCount('produtos')
            ->when($request->filled('busca'), function ($query) use ($request) {
                $busca = $request->busca;

                $query->where(function ($sub) use ($busca) {
                    $sub->where('codigo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhere('setor', 'like', "%{$busca}%")
                        ->orWhere('rua', 'like', "%{$busca}%");
                });
            })
            ->when($request->filled('tipo_localizacao'), function ($query) use ($request) {
                $query->where('tipo_localizacao', $request->tipo_localizacao);
            })
            ->when($request->filled('ativo'), function ($query) use ($request) {
                $query->where('ativo', $request->ativo);
            })
            ->orderBy('ordem_coleta')
            ->orderBy('codigo')
            ->paginate(20)
            ->withQueryString();

        return view('localizacoes-estoque.index', compact('localizacoes'));
    }

    public function create()
    {
        return view('localizacoes-estoque.create', [
            'tipos' => $this->tiposLocalizacao(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);

        LocalizacaoEstoque::create($validated);

        return redirect()
            ->route('localizacoes-estoque.index')
            ->with('success', 'Localização de estoque cadastrada com sucesso.');
    }

    public function edit(LocalizacaoEstoque $localizacaoEstoque)
    {
        return view('localizacoes-estoque.edit', [
            'localizacao' => $localizacaoEstoque,
            'tipos' => $this->tiposLocalizacao(),
        ]);
    }

    public function update(Request $request, LocalizacaoEstoque $localizacaoEstoque)
    {
        $validated = $this->validar($request, $localizacaoEstoque->id);

        $localizacaoEstoque->update($validated);

        return redirect()
            ->route('localizacoes-estoque.index')
            ->with('success', 'Localização de estoque atualizada com sucesso.');
    }

    public function destroy(LocalizacaoEstoque $localizacaoEstoque)
    {
        if ($localizacaoEstoque->produtos()->exists()) {
            return back()
                ->with('error', 'Esta localização possui produtos vinculados e não pode ser excluída.');
        }

        $localizacaoEstoque->delete();

        return redirect()
            ->route('localizacoes-estoque.index')
            ->with('success', 'Localização de estoque excluída com sucesso.');
    }

    private function validar(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                'unique:localizacoes_estoque,codigo,' . $id,
            ],
            'descricao' => ['nullable', 'string', 'max:255'],
            'tipo_localizacao' => ['required', 'string', 'max:50'],
            'setor' => ['nullable', 'string', 'max:100'],
            'rua' => ['nullable', 'string', 'max:50'],
            'lado' => ['nullable', 'string', 'max:50'],
            'modulo' => ['nullable', 'string', 'max:50'],
            'prateleira' => ['nullable', 'string', 'max:50'],
            'nivel' => ['nullable', 'string', 'max:50'],
            'ordem_coleta' => ['required', 'integer', 'min:1'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }

    private function tiposLocalizacao(): array
    {
        return [
            'Galpao',
            'Patio',
            'Area Externa',
            'Pulmao',
            'Picking',
            'Devolucao',
            'Quarentena',
        ];
    }
}