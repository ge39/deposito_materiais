<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    // Listar todos os fornecedores
    public function index()
    {
        // Busca apenas fornecedores ativos
        //$fornecedores = Fornecedor::where('ativo', 1)->get();
        
        // Se quiser paginar (opcional)
        $fornecedores = Fornecedor::where('ativo', 1)->paginate(15);
        return view('fornecedores.index', compact('fornecedores'));
    }

    // Formulário de criação
    public function create()
    {
        return view('fornecedores.create');
    }

    // Salvar novo fornecedor
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:20',
            'cnpj' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        Fornecedor::create($request->all());
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor criado com sucesso!');
    }

    // Mostrar detalhes de um fornecedor
    public function show(Fornecedor $fornecedore)
    {
        // Padronizando variável para view
        $fornecedor = $fornecedore;
        return view('fornecedores.show', compact('fornecedor'));
    }

    // Formulário de edição
    public function edit(Fornecedor $fornecedore)
    {
        // Padronizando variável para view
        $fornecedor = $fornecedore;
        return view('fornecedores.edit', compact('fornecedor'));
    }

    // Atualizar fornecedor
    public function update(Request $request, Fornecedor $fornecedore)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'tipo'=> 'nullable|string|max:20',
            'cnpj' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $fornecedor = $fornecedore;
        $fornecedor->update($request->all());

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    // Deletar fornecedor
    public function destroy(Fornecedor $fornecedore)
    {
        $fornecedor = $fornecedore;
        $fornecedor->delete();

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor deletado com sucesso!');
    }

    // Ativar Fornecedor
    public function ativar(Fornecedor $fornecedore)
    {
        $fornecedore->ativo = 1;
        $fornecedore->save();
        return redirect()->route('fornecedores.inativos')->with('success', 'Fornecedor ativado.');
    }

    // Desativar Fornecedor
    public function desativar($id)
    {
        // Busca o fornecedor pelo ID
        $fornecedor = Fornecedor::find($id);

        if (!$fornecedor) {
            return redirect()->route('fornecedores.index')
                ->with('error', 'Fornecedor não encontrado.');
        }

        // Altera apenas o campo ativo
        $fornecedor->ativo = 0;
        $fornecedor->save(); // salva a alteração

        return redirect()->route('fornecedores.index')
            ->with('success', 'Fornecedor desativado com sucesso.');
    }

}
