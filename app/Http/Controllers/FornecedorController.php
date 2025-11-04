<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\Filterable;

class FornecedorController extends Controller
{
    use Filterable;

    /**
     * Construtor: aplica autenticação e autorização via Gate.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'can:gerenciar-fornecedores']);
    }

    /**
     * Campos permitidos para filtro (usado pelo trait Filterable)
     */
    protected function filterableFields(): array
    {
        return [
            'nome',
            'cnpj',
            'email',
            'ativo',
        ];
    }

    /**
     * Lista fornecedores ativos com filtros e paginação.
     */
    public function index(Request $request)
    {
        $query = Fornecedor::query()->where('ativo', 1)->orderBy('nome');

        // aplica filtros dinamicamente (nome, cnpj, email, ativo)
        $query = $this->applyFilters($query, $request);

        $fornecedores = $query->paginate(15)->appends($request->query());

        return view('fornecedores.index', compact('fornecedores'));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        return view('fornecedores.create');
    }

    /**
     * Armazena novo fornecedor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20|unique:fornecedores,cnpj',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
        ]);

        $validated['ativo'] = 1;

        Fornecedor::create($validated);

        return redirect()->route('fornecedores.index')
                         ->with('success', 'Fornecedor criado com sucesso!');
    }

    /**
     * Formulário de edição.
     */
    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.edit', compact('fornecedor'));
    }

    /**
     * Atualiza fornecedor.
     */
    public function update(Request $request, Fornecedor $fornecedor)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20|unique:fornecedores,cnpj,' . $fornecedor->id,
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
        ]);

        $fornecedor->update($validated);

        return redirect()->route('fornecedores.index')
                         ->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Desativa fornecedor.
     */
    public function desativar(Fornecedor $fornecedor)
    {
        $fornecedor->update(['ativo' => 0]);

        return redirect()->route('fornecedores.index')
                         ->with('success', 'Fornecedor desativado com sucesso!');
    }

    /**
     * Reativa fornecedor.
     */
    public function reativar(Fornecedor $fornecedor)
    {
        $fornecedor->update(['ativo' => 1]);

        return redirect()->route('fornecedores.inativos')
                         ->with('success', 'Fornecedor reativado com sucesso!');
    }

    /**
     * Lista fornecedores inativos.
     */
    public function inativos(Request $request)
    {
        $query = Fornecedor::query()->where('ativo', 0)->orderBy('nome');
        $query = $this->applyFilters($query, $request);

        $fornecedores = $query->paginate(15)->appends($request->query());

        return view('fornecedores.inativos', compact('fornecedores'));
    }

    /**
     * Pesquisa rápida de fornecedores.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        $fornecedores = Fornecedor::where('ativo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('nome', 'LIKE', "%{$query}%")
                  ->orWhere('cnpj', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->orderBy('nome')
            ->paginate(15)
            ->appends($request->query());

        return view('fornecedores.index', compact('fornecedores'));
    }
}
