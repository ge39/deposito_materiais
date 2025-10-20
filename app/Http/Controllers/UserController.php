<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Funcionario;

class UserController extends Controller
{
    // Lista todos os usuários ativos
    public function index()
    {
        // $users = User::ativos()->get();
        // return view('users.index', compact('users'));
        $users = user::where('ativo', 1)->paginate(15);
        return view('users.index', compact('users'));
    }
    

    // Mostra o formulário de criação
    public function create()
    {
        return view('users.create');
    }

    // Busca funcionário pelo CPF
    public function buscarFuncionario($cpf)
    {
        // Remove máscara
        $cpf = preg_replace('/\D/', '', $cpf);

        $funcionario = Funcionario::where('cpf', $cpf)->first();

        if ($funcionario) {
            return response()->json([
                'success' => true,
                'data' => $funcionario
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum funcionário encontrado com este CPF.'
            ]);
        }
    }

    // Armazena novo usuário
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            // 'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed',
            'funcionario_id' => 'required'
        ]);

        $user = new User();
        $user->name = $request->name;
        // $user->email = $request->email;
        $user->password = $request->password; // criptografado automaticamente pelo model
        $user->funcionario_id = $request->funcionario_id;
        $user->ativo = 1;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    // Mostra o formulário de edição (opcional)
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // Atualiza usuário (opcional)
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            // 'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:4|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if($request->password){
            $user->password = $request->password; // criptografado automaticamente
        }
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    // Desativa um usuário
    public function desativa(User $user)
    {
        $user->ativo = 0;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário desativado com sucesso!');
    }
}
