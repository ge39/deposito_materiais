<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;

class EmpresaController extends Controller
{
    // Lista todas as empresas Ativas
    public function index()
    {
        $empresas = Empresa::where('ativo', true)->orderBy('id')->get();
        return view('empresa.index', compact('empresas'));
    }

    // Mostra o formulário de criação
    public function create()
    {
        return view('empresa.create');
    }

    // Salva nova empresa
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18|unique:empresa,cnpj',
            'inscricao_estadual' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:50',
            'bairro' => 'nullable|string|max:50',
            'cidade' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'site' => 'nullable|string|max:100',
            'ativo' => 'nullable|boolean',
        ]);

        Empresa::create([
            'nome' => $request->nome,
            'cnpj' => $request->cnpj,
            'inscricao_estadual' => $request->inscricao_estadual,
            'endereco' => $request->endereco,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'bairro' => $request->bairro,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'cep' => $request->cep,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'site' => $request->site,
            'ativo' => $request->ativo ?? true,
        ]);

        return redirect()->route('empresa.index')
            ->with('success', 'Empresa criada com sucesso!');
    }

    // Mostra o formulário de edição
    public function edit(Empresa $empresa)
    {
        return view('empresa.edit', compact('empresa'));
    }

    // Atualiza a empresa
    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18|unique:empresa,cnpj,' . $empresa->id,
            'inscricao_estadual' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:50',
            'bairro' => 'nullable|string|max:50',
            'cidade' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:2',
            'cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'site' => 'nullable|string|max:100',
            'ativo' => 'nullable|boolean',
        ]);

        $empresa->update([
            'nome' => $request->nome,
            'cnpj' => $request->cnpj,
            'inscricao_estadual' => $request->inscricao_estadual,
            'endereco' => $request->endereco,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'bairro' => $request->bairro,
            'cidade' => $request->cidade,
            'estado' => $request->estado,
            'cep' => $request->cep,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'site' => $request->site,
            'ativo' => $request->ativo ?? $empresa->ativo,
        ]);

        return redirect()->route('empresa.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    // Desativa a empresa
    public function desativar(Empresa $empresa)
    {
        $empresa->ativo = false;
        $empresa->save();

        return redirect()->route('empresa.index')
            ->with('success', 'Empresa desativada com sucesso!');
    }

    // Ativa a empresa
    public function ativar(Empresa $empresa)
    {
        $empresa->ativo = true;
        $empresa->save();

        return redirect()->route('empresa.index')
            ->with('success', 'Empresa ativada com sucesso!');
    }

    // Lista empresas desativadas
    public function desativadas()
    {
        $empresas = Empresa::where('ativo', false)->orderBy('id', 'asc')->get();
        return view('empresa.desativadas', compact('empresas'));
    }
}
