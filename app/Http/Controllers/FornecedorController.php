<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FornecedorController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');

        // Bloqueio de acesso: apenas admin e gerente
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!in_array($user->nivel_acesso, ['admin', 'gerente'])) {
                abort(403, 'Acesso negado!');
            }
            return $next($request);
        });
    }


    public function index(Request $request)
    {
    $busca = $request->input('busca');

    $fornecedores = Fornecedor::where('ativo', 1)
        ->when($busca, function ($query, $busca) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('telefone', 'like', "%{$busca}%")
                  ->orWhere('cnpj', 'like', "%{$busca}%");
                  
            });
        })
        ->orderBy('nome', 'asc')
        ->paginate(9)
        ->withQueryString(); // mantém o termo de busca na paginação

    return view('fornecedores.index', compact('fornecedores', 'busca'));

    
      
        $fornecedores = Fornecedor::where('ativo', 1)
        ->orderBy('nome', 'asc')
        ->paginate(15); // Mostra 9 fornecedores por página

        return view('fornecedores.index', compact('fornecedores'));
    }

    
    public function create()
    {
         $this->middleware('auth');

        return view('fornecedores.create');
    }

    public function store(Request $request)
    {
         $this->middleware('auth');

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
        ]);

        Fornecedor::create($validated);
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }

    public function edit($id)
    {
         $this->middleware('auth');

         $fornecedor = Fornecedor::findOrFail($id);
        return view('fornecedores.edit', compact('fornecedor'));
    }

    // public function update(Request $request, Fornecedor $fornecedor)
    // {
    //      $this->middleware('auth');

    //     $validated = $request->validate([
    //         'nome' => 'required|string|max:255',
    //         'email' => 'nullable|email',
    //         'telefone' => 'nullable|string|max:20',
    //         'endereco' => 'nullable|string|max:255',

    //     ]);

    //     $fornecedor->update($validated);
    //     return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    // }
    public function update(Request $request, $id)
    {
        // ✅ Validação dos campos
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'cnpj' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string|max:500',
        ]);

        // ✅ Busca o fornecedor
        $fornecedor = Fornecedor::findOrFail($id);

        // ✅ Atualiza apenas os campos validados
        $fornecedor->update($validated);

        // ✅ Retorna para a lista com mensagem de sucesso
        return redirect()
            ->route('fornecedores.index')
            ->with('success', 'Fornecedor atualizado com sucesso!');
    }


   public function inativos()
    {
        // Busca todos os fornecedores inativos (status = 0)
        $fornecedores = Fornecedor::where('ativo', 0)
            ->orderBy('nome', 'asc')
            ->get();

        // Retorna a view com os dados
        return view('fornecedores.inativos', compact('fornecedores'));
    }

    public function desativar($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        $fornecedor->update(['ativo' => 0]);

        return redirect()
            ->route('fornecedores.index')
            ->with('success', 'Fornecedor desativado com sucesso!');
    }
    
    public function ativar($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        $fornecedor->update(['ativo' => 1]);

        return redirect()
            ->route('fornecedores.inativos')
            ->with('success', 'Fornecedor reativado com sucesso!');
    }


}
