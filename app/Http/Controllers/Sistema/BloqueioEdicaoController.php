<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Services\Sistema\BloqueioEdicaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BloqueioEdicaoController extends Controller
{
    public function adquirir(
        Request $request,
        BloqueioEdicaoService $bloqueioService
    ): JsonResponse {
        $dados = $request->validate([
            'recurso_tipo' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9._-]+$/',
            ],

            'recurso_id' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $bloqueio = $bloqueioService
            ->adquirirOuConsultar(
                $dados['recurso_tipo'],
                (int) $dados['recurso_id'],
                (int) $request->user()->id,
                $request->session()->getId()
            );

        $podeEditar = $bloqueioService
            ->podeEditar(
                $bloqueio,
                (int) $request->user()->id,
                $request->session()->getId()
            );

        return response()
            ->json([
                'pode_editar' =>
                    $podeEditar,

                'token' =>
                    $podeEditar
                        ? $bloqueio->token
                        : null,

                'usuario_id' =>
                    (int) $bloqueio
                        ->usuario_id,

                'mensagem' =>
                    $podeEditar
                        ? null
                        : $bloqueioService
                            ->mensagemBloqueio(
                                $bloqueio
                            ),
            ])
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate'
            );
    }

    public function consultar(
        Request $request,
        BloqueioEdicaoService $bloqueioService
    ): JsonResponse {
        $dados = $request->validate([
            'recurso_tipo' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9._-]+$/',
            ],

            'recurso_id' => [
                'nullable',
                'required_without:recursos_ids',
                'integer',
                'min:1',
            ],

            'recursos_ids' => [
                'nullable',
                'required_without:recurso_id',
                'array',
                'max:100',
            ],

            'recursos_ids.*' => [
                'integer',
                'min:1',
            ],
        ]);

        $recursosIds = collect(
            $dados['recursos_ids']
            ?? [
                $dados['recurso_id']
                ?? null,
            ]
        )
            ->map(
                fn ($recursoId) =>
                    (int) $recursoId
            )
            ->filter()
            ->unique()
            ->values();

        $bloqueios = $bloqueioService
            ->consultarAtivos(
                $dados['recurso_tipo'],
                $recursosIds->all()
            );

        $usuarioId = (int) $request
            ->user()
            ->id;

        $sessaoId = $request
            ->session()
            ->getId();

        $resultados = $recursosIds
            ->map(function (
                int $recursoId
            ) use (
                $bloqueios,
                $bloqueioService,
                $usuarioId,
                $sessaoId
            ) {
                $bloqueio = $bloqueios->get(
                    $recursoId
                );

                if (! $bloqueio) {
                    return [
                        'recurso_id' =>
                            $recursoId,

                        'bloqueado' =>
                            false,

                        'pode_editar' =>
                            true,

                        'usuario_id' =>
                            null,

                        'mensagem' =>
                            null,
                    ];
                }

                $podeEditar = $bloqueioService
                    ->podeEditar(
                        $bloqueio,
                        $usuarioId,
                        $sessaoId
                    );

                return [
                    'recurso_id' =>
                        $recursoId,

                    'bloqueado' =>
                        true,

                    'pode_editar' =>
                        $podeEditar,

                    'usuario_id' =>
                        (int) $bloqueio
                            ->usuario_id,

                    'mensagem' =>
                        $podeEditar
                            ? null
                            : $bloqueioService
                                ->mensagemBloqueio(
                                    $bloqueio
                                ),
                ];
            })
            ->values();

        return response()
            ->json([
                'resultados' =>
                    $resultados,
            ])
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate'
            );
    }

    public function renovar(
        Request $request,
        BloqueioEdicaoService $bloqueioService
    ): JsonResponse {
        $dados = $this->validarRequisicao(
            $request
        );

        $bloqueio = $bloqueioService->renovar(
            $dados['recurso_tipo'],
            (int) $dados['recurso_id'],
            (int) $request->user()->id,
            $request->session()->getId(),
            $dados['token']
        );

        return response()->json([
            'renovado' =>
                true,

            'expira_em' =>
                $bloqueio->expira_em?->toIso8601String(),
        ]);
    }

    public function liberar(
        Request $request,
        BloqueioEdicaoService $bloqueioService
    ): JsonResponse {
        $dados = $this->validarRequisicao(
            $request
        );

        $bloqueioService->liberar(
            $dados['recurso_tipo'],
            (int) $dados['recurso_id'],
            (int) $request->user()->id,
            $request->session()->getId(),
            $dados['token']
        );

        return response()->json([
            'liberado' =>
                true,
        ]);
    }

    private function validarRequisicao(
        Request $request
    ): array {
        return $request->validate([
            'recurso_tipo' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9._-]+$/',
            ],

            'recurso_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'token' => [
                'required',
                'uuid',
            ],
        ]);
    }
}