<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Funcionario;

class UserController extends Controller
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

    // Mostrar detalhes de um usuário
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // Mostra o formulário de edição (opcional)
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // Atualiza usuário (opcional)
    public function update(Request $request, User $user)
    {
       // Validação
        $validated = $request->validate([
            'nivel_acesso' => 'required|in:admin,vendedor,gerente',
            'ativo' => 'required|boolean',
            'password' => 'nullable|min:4|same:password_confirmation',
        ], [
            'nivel_acesso.required' => 'O nível de acesso é obrigatório.',
            'ativo.required' => 'O status do usuário é obrigatório.',
            'password.min' => 'A senha deve conter pelo menos 4 caracteres.',
            'password.same' => 'As senhas não são iguais.',
        ]);

        // Atualiza apenas o nível de acesso e status
        $user->nivel_acesso = $request->nivel_acesso;
        $user->ativo = $request->ativo;

        // Atualiza a senha apenas se o campo for preenchido
        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);// criptografado automaticamente
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
