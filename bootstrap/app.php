<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    // ->withExceptions(function (Exceptions $exceptions) {

    //     $exceptions->render(function (NotFoundHttpException $e, Request $request) {

    //         if ($request->is('pdv*')) {
    //             return response()->view('errors.404-pdv', [], 404);
    //         }

    //         return response()->view('errors.404-ajuda', [], 404);
    //     });
    // })


    ->withExceptions(function (Exceptions $exceptions) {

    $exceptions->render(function (
        NotFoundHttpException $e,
        Request $request
    ) {

        // Se quiser tratar diferente quando NÃO estiver logado
        if (!Auth::check()) {
            return response()->view('errors.rota-invalida', [
                'url' => $request->fullUrl()
            ], 404);
        }

        // 🔴 Se for rota do PDV
        if ($request->is('pdv*')) {
            return response()->view('errors.404-pdv', [
                'url' => $request->fullUrl()
            ], 404);
        }

        // 🔵 Demais rotas
        return response()->view('errors.rota-invalida', [
            'url' => $request->fullUrl()
        ], 404);
    });

})
    ->create();