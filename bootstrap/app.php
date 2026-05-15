<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\QueryException; // 👈 ADICIONADO
use Illuminate\Http\Request;
use App\Http\Middleware\IdentificaTerminal;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'terminal' => IdentificaTerminal::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 🔴 [TRATAMENTO NOVO] CAPTURA QUEDA DE CONEXÃO COM O BANCO DE DADOS
        $exceptions->render(function (QueryException $e, Request $request) {
            $mensagem = $e->getMessage();
            $codigo = $e->getCode();

            // Captura erros clássicos de conexão recusada ou indisponível (SQLSTATE HY000 / código 2002)
            if ($codigo === 'HY000' || str_contains($mensagem, '2002') || str_contains($mensagem, 'Connection refused')) {
                \Log::critical('Banco de dados offline detectado via Request: ' . $request->fullUrl());
                
                // Retorna a view amigável com status HTTP 503 (Serviço Indisponível)
                return response()->view('errors.database', [], 503);
            }
        });

        // 🔵 [SEU TRATAMENTO EXISTENTE] CAPTURA ROTAS NÃO ENCONTRADAS (404)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (!Auth::check()) {
                return response()->view('errors.rota-invalida', [
                    'url' => $request->fullUrl()
                ], 404);
            }

            if ($request->is('pdv*')) {
                return response()->view('errors.404-pdv', [
                    'url' => $request->fullUrl()
                ], 404);
            }

            return response()->view('errors.rota-invalida', [
                'url' => $request->fullUrl()
            ], 404);
        });
    })
    ->create();
