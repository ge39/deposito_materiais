<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('funcionario')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $funcionarios = Funcionario::all();
        return view('usuarios.create', compact('funcionarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'nome_usuario' => 'required|string|max:50|unique:usuarios',
            'email' => 'nullable|email|max:100',
            'senha' => 'required|string|min:6',
            'nivel' => 'required|in:admin,gerente,vendedor',
            'status' => 'required|in:ativo,inativo',
        ]);

        $data['senha'] = Hash::make($data['senha']);

        Usuario::create($data);
        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function show(Usuario $usuario)
    {
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(Usuario $usuario)
    {
        $funcionarios = Funcionario::all();
        return view('usuarios.edit', compact('usuario', 'funcionarios'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'nome_usuario' => 'required|string|max:50|unique:usuarios,nome_usuario,' . $usuario->id,
            'email' => 'nullable|email|max:100',
            'senha' => 'nullable|string|min:6',
            'nivel' => 'required|in:admin,gerente,vendedor',
            'status' => 'required|in:ativo,inativo',
        ]);

        if (!empty($data['senha'])) {
            $data['senha'] = Hash::make($data['senha']);
        } else {
            unset($data['senha']);
        }

        $usuario->update($data);
        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuário removido com sucesso.');
    }
}
