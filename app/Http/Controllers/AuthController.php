<?php

namespace App\Http\Controllers;

use App\Models\EdicaoBloqueio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Mostrar formulário de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Processar login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'ativo' => 1 // só usuários ativos
        ])) {
            $request->session()->regenerate();

            $usuario = $request->user();
            $usuarioId = (int) $usuario->getAuthIdentifier();
            $sessaoHash = hash(
                'sha256',
                $request->session()->getId()
            );

            DB::transaction(function () use (
                $usuarioId,
                $sessaoHash
            ) {
                DB::table('users')
                    ->where('id', $usuarioId)
                    ->lockForUpdate()
                    ->first();

                DB::table('users')
                    ->where('id', $usuarioId)
                    ->update([
                        'sessao_ativa_hash' => $sessaoHash,
                    ]);

                EdicaoBloqueio::query()
                    ->where('usuario_id', $usuarioId)
                    ->where(function ($query) use ($sessaoHash) {
                        $query
                            ->whereNull('sessao_hash')
                            ->orWhere(
                                'sessao_hash',
                                '<>',
                                $sessaoHash
                            );
                    })
                    ->delete();
            }, 3);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'As credenciais informadas estão incorretas.',
        ])->withInput();
    }

    // Logout
    public function logout(Request $request)
    {
        $usuario = $request->user();

        if ($usuario) {
            $usuarioId = (int) $usuario->getAuthIdentifier();
            $sessaoHash = hash(
                'sha256',
                $request->session()->getId()
            );

            DB::transaction(function () use (
                $usuarioId,
                $sessaoHash
            ) {
                DB::table('users')
                    ->where('id', $usuarioId)
                    ->where(
                        'sessao_ativa_hash',
                        $sessaoHash
                    )
                    ->update([
                        'sessao_ativa_hash' => null,
                    ]);

                EdicaoBloqueio::query()
                    ->where('usuario_id', $usuarioId)
                    ->where('sessao_hash', $sessaoHash)
                    ->delete();
            }, 3);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}