<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Terminal;

class IdentificaTerminal
{
    public function handle(Request $request, Closure $next)
    {
        $cookieName = 'pdv_terminal_uuid';
        $uuid = $request->cookie($cookieName);

        if (!$uuid) {
            $uuid = (string) Str::uuid();

            // Salva cookie por 30 dias
            cookie()->queue(cookie($cookieName, $uuid, 60*24*30));
        }

        // Salva ou atualiza terminal
        $terminal = Terminal::updateOrCreate(
            ['uuid' => $uuid],
            [
                'identificador' => 'Terminal ' . substr($uuid, 0, 6),
                'descricao' => null,
                'user_agent' => $request->header('User-Agent'),
                'primeiro_acesso' => now(),
                'ultimo_acesso' => now(),
                'ativo' => 1,
            ]
        );

        $request->attributes->set('terminal', $terminal);

        return $next($request);
    }
}
