<?php

namespace App\Http\Middleware;

use App\Services\Sistema\BloqueioEdicaoService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BloqueioEdicaoMiddleware
{
    public function __construct(
        private readonly BloqueioEdicaoService $bloqueioService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $recursoTipo,
        string $parametro,
        string $modo = 'adquirir',
        string $atributo = 'id'
    ): Response {
        $usuarioId = (int) (
            $request->user()?->id
            ?? 0
        );

        if ($usuarioId <= 0) {
            throw new HttpException(
                401,
                'É necessário autenticar-se para editar este arquivo.'
            );
        }

        $recursoId = $this->resolverRecursoId(
            $request,
            $parametro,
            $atributo
        );

        /*
        * Algumas telas de seleção podem ser abertas sem um registro.
        * O bloqueio passa a ser obrigatório quando o ID é informado.
        */
        if (
            $recursoId <= 0
            && $modo === 'adquirir'
        ) {
            return $next($request);
        }

        if ($recursoId <= 0) {
            throw new HttpException(
                423,
                'Não foi possível identificar o arquivo vinculado à edição.'
            );
        }

        $sessaoId = $request
            ->session()
            ->getId();

        if ($modo === 'adquirir') {
            $bloqueio = $this
                ->bloqueioService
                ->adquirirOuConsultar(
                    $recursoTipo,
                    $recursoId,
                    $usuarioId,
                    $sessaoId
                );

            $podeEditar = $this
                ->bloqueioService
                ->podeEditar(
                    $bloqueio,
                    $usuarioId,
                    $sessaoId
                );

            $mensagem = $podeEditar
                ? null
                : $this
                    ->bloqueioService
                    ->mensagemBloqueio(
                        $bloqueio
                    );

            $dadosCompartilhados = [
                'bloqueioEdicao' =>
                    $bloqueio,

                'podeEditarArquivo' =>
                    $podeEditar,

                'mensagemBloqueioEdicao' =>
                    $mensagem,

                'bloqueioEdicaoToken' =>
                    $podeEditar
                        ? $bloqueio->token
                        : null,

                'bloqueioEdicaoRecursoTipo' =>
                    $recursoTipo,

                'bloqueioEdicaoRecursoId' =>
                    $recursoId,
            ];

            View::share(
                $dadosCompartilhados
            );

            foreach (
                $dadosCompartilhados
                as $chave => $valor
            ) {
                $request->attributes->set(
                    $chave,
                    $valor
                );
            }

            return $next($request);
        }

        if ($modo !== 'validar') {
            throw new HttpException(
                500,
                'O modo do bloqueio de edição é inválido.'
            );
        }

        $token = trim(
            (string) (
                $request->input(
                    'bloqueio_edicao_token'
                )
                ?? $request->header(
                    'X-Bloqueio-Edicao-Token'
                )
                ?? ''
            )
        );

        if ($token === '') {
            throw new HttpException(
                423,
                'A sessão de edição não foi identificada. Atualize a página antes de continuar.'
            );
        }

        /*
        * A linha do bloqueio permanece travada até o término da ação.
        * A validação, a operação do controller e a eventual liberação
        * pertencem à mesma transação.
        */
        return DB::transaction(function () use (
            $request,
            $next,
            $recursoTipo,
            $recursoId,
            $usuarioId,
            $sessaoId,
            $token
        ) {
            $bloqueio = $this
                ->bloqueioService
                ->validarEdicao(
                    $recursoTipo,
                    $recursoId,
                    $usuarioId,
                    $sessaoId,
                    $token
                );

            $request->attributes->set(
                'bloqueioEdicao',
                $bloqueio
            );

            $response = $next(
                $request
            );

            $deveLiberar = (
                $request->attributes->get(
                    'liberarBloqueioEdicaoAoConcluir'
                )
                === true
            );

            if ($deveLiberar) {
                $this
                    ->bloqueioService
                    ->liberar(
                        $recursoTipo,
                        $recursoId,
                        $usuarioId,
                        $sessaoId,
                        $token
                    );

                $request->attributes->set(
                    'bloqueioEdicaoLiberado',
                    true
                );
            }

            return $response;
        }, 3);
    }

    private function resolverRecursoId(
        Request $request,
        string $parametro,
        string $atributo
    ): int {
        $valor = $request->route(
            $parametro
        );

        if ($valor === null) {
            $valor = $request->input(
                $parametro
            );
        }

        if ($valor instanceof Model) {
            $valor = $atributo === 'id'
                ? $valor->getKey()
                : data_get(
                    $valor,
                    $atributo
                );
        } elseif (
            is_object($valor)
            && $atributo !== 'id'
        ) {
            $valor = data_get(
                $valor,
                $atributo
            );
        }

        return (int) $valor;
    }
}