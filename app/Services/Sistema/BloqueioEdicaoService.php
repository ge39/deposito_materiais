<?php

namespace App\Services\Sistema;

use App\Models\EdicaoBloqueio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BloqueioEdicaoService
{
    public const MINUTOS_EXPIRACAO = 5;

    public function adquirirOuConsultar(
            string $recursoTipo,
            int $recursoId,
            int $usuarioId,
            string $sessaoId
        ): EdicaoBloqueio {
        $recursoTipo = $this->normalizarRecursoTipo(
            $recursoTipo
        );

        $this->validarIdentificadores(
            $recursoId,
            $usuarioId,
            $sessaoId
        );

        $sessaoHash = $this->gerarHashSessao(
            $sessaoId
        );

        return DB::transaction(function () use (
            $recursoTipo,
            $recursoId,
            $usuarioId,
            $sessaoHash
        ) {
            $agora = now();

            $tokenNovo = (string) Str::uuid();

            /*
            * A chave única recurso_tipo + recurso_id torna esta
            * aquisição atômica quando duas requisições tentam
            * abrir o mesmo registro simultaneamente.
            */
            EdicaoBloqueio::query()
                ->insertOrIgnore([
                    'recurso_tipo' =>
                        $recursoTipo,

                    'recurso_id' =>
                        $recursoId,

                    'usuario_id' =>
                        $usuarioId,

                    'sessao_hash' =>
                        $sessaoHash,

                    'token' =>
                        $tokenNovo,

                    'bloqueado_em' =>
                        $agora,

                    'renovado_em' =>
                        $agora,

                    'expira_em' =>
                        $agora
                            ->copy()
                            ->addMinutes(
                                self::MINUTOS_EXPIRACAO
                            ),

                    'created_at' =>
                        $agora,

                    'updated_at' =>
                        $agora,
                ]);

            $bloqueio = EdicaoBloqueio::query()
                ->where(
                    'recurso_tipo',
                    $recursoTipo
                )
                ->where(
                    'recurso_id',
                    $recursoId
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($bloqueio->expirou()) {
                $bloqueio->update([
                    'usuario_id' =>
                        $usuarioId,

                    'sessao_hash' =>
                        $sessaoHash,

                    'token' =>
                        $tokenNovo,

                    'bloqueado_em' =>
                        $agora,

                    'renovado_em' =>
                        $agora,

                    'expira_em' =>
                        $agora
                            ->copy()
                            ->addMinutes(
                                self::MINUTOS_EXPIRACAO
                            ),
                ]);
            } elseif (
                $this->pertenceAoContexto(
                    $bloqueio,
                    $usuarioId,
                    $sessaoHash
                )
            ) {
                /*
                * O token permanece estável enquanto o bloqueio
                * pertencer ao mesmo usuário e à mesma sessão.
                *
                * A navegação e a liberação serão controladas
                * pelo layout, sem invalidar páginas legítimas
                * do mesmo fluxo operacional.
                */
                $bloqueio->update([
                    'renovado_em' =>
                        $agora,

                    'expira_em' =>
                        $agora
                            ->copy()
                            ->addMinutes(
                                self::MINUTOS_EXPIRACAO
                            ),
                ]);
            }

            return $bloqueio
                ->fresh()
                ->load('usuario');
        }, 3);
    }

    public function podeEditar(
        EdicaoBloqueio $bloqueio,
        int $usuarioId,
        string $sessaoId
    ): bool {
        if ($bloqueio->expirou()) {
            return false;
        }

        return $this->pertenceAoContexto(
            $bloqueio,
            $usuarioId,
            $this->gerarHashSessao($sessaoId)
        );
    }

    public function validarEdicao(
        string $recursoTipo,
        int $recursoId,
        int $usuarioId,
        string $sessaoId,
        string $token
    ): EdicaoBloqueio {
        $recursoTipo = $this->normalizarRecursoTipo(
            $recursoTipo
        );

        $sessaoHash = $this->gerarHashSessao(
            $sessaoId
        );

        return DB::transaction(function () use (
            $recursoTipo,
            $recursoId,
            $usuarioId,
            $sessaoHash,
            $token
        ) {
            $bloqueio = $this->buscarBloqueioParaAtualizacao(
                $recursoTipo,
                $recursoId
            );

            $this->garantirPropriedade(
                $bloqueio,
                $usuarioId,
                $sessaoHash,
                $token
            );

            return $bloqueio->load('usuario');
        }, 3);
    }

    public function renovar(
        string $recursoTipo,
        int $recursoId,
        int $usuarioId,
        string $sessaoId,
        string $token
    ): EdicaoBloqueio {
        $recursoTipo = $this->normalizarRecursoTipo(
            $recursoTipo
        );

        $sessaoHash = $this->gerarHashSessao(
            $sessaoId
        );

        return DB::transaction(function () use (
            $recursoTipo,
            $recursoId,
            $usuarioId,
            $sessaoHash,
            $token
        ) {
            $bloqueio = $this->buscarBloqueioParaAtualizacao(
                $recursoTipo,
                $recursoId
            );

            $this->garantirPropriedade(
                $bloqueio,
                $usuarioId,
                $sessaoHash,
                $token
            );

            $agora = now();

            $bloqueio->update([
                'renovado_em' =>
                    $agora,

                'expira_em' =>
                    $agora->copy()->addMinutes(
                        self::MINUTOS_EXPIRACAO
                    ),
            ]);

            return $bloqueio
                ->fresh()
                ->load('usuario');
        }, 3);
    }

    public function liberar(
        string $recursoTipo,
        int $recursoId,
        int $usuarioId,
        string $sessaoId,
        string $token
    ): void {
        $recursoTipo = $this->normalizarRecursoTipo(
            $recursoTipo
        );

        $sessaoHash = $this->gerarHashSessao(
            $sessaoId
        );

        DB::transaction(function () use (
            $recursoTipo,
            $recursoId,
            $usuarioId,
            $sessaoHash,
            $token
        ) {
            $bloqueio = EdicaoBloqueio::query()
                ->where(
                    'recurso_tipo',
                    $recursoTipo
                )
                ->where(
                    'recurso_id',
                    $recursoId
                )
                ->lockForUpdate()
                ->first();

            /*
            * A liberação é idempotente. Se outra requisição
            * legítima da mesma página já removeu o bloqueio,
            * o resultado esperado já foi alcançado.
            */
            if (! $bloqueio) {
                return;
            }

            $this->garantirPropriedade(
                $bloqueio,
                $usuarioId,
                $sessaoHash,
                $token
            );

            $bloqueio->delete();
        }, 3);
    }

    public function liberarExpirados(): int
    {
        return EdicaoBloqueio::query()
            ->where(
                'expira_em',
                '<=',
                now()
            )
            ->delete();
    }

    public function consultarAtivos(
        string $recursoTipo,
        array $recursosIds
    ): Collection {
        $recursoTipo = $this->normalizarRecursoTipo(
            $recursoTipo
        );

        $recursosIds = collect(
            $recursosIds
        )
            ->map(
                fn ($recursoId) =>
                    (int) $recursoId
            )
            ->filter(
                fn (int $recursoId) =>
                    $recursoId > 0
            )
            ->unique()
            ->values();

        if ($recursosIds->isEmpty()) {
            return collect();
        }

        return EdicaoBloqueio::query()
            ->with('usuario')
            ->where(
                'recurso_tipo',
                $recursoTipo
            )
            ->whereIn(
                'recurso_id',
                $recursosIds
            )
            ->where(
                'expira_em',
                '>',
                now()
            )
            ->get()
            ->keyBy(
                fn (EdicaoBloqueio $bloqueio) =>
                    (int) $bloqueio->recurso_id
            );
    }

    public function mensagemBloqueio(
        EdicaoBloqueio $bloqueio
    ): string {
        $bloqueio->loadMissing('usuario');

        $nomeUsuario = trim(
            (string) (
                $bloqueio->usuario?->nome
                ?? $bloqueio->usuario?->name
                ?? "Usuário #{$bloqueio->usuario_id}"
            )
        );

        return
            "Este arquivo está sendo editado por {$nomeUsuario}. "
            . 'Para editá-lo, peça ao usuário que finalize a edição e feche a página.';
    }

    private function buscarBloqueioParaAtualizacao(
        string $recursoTipo,
        int $recursoId
    ): EdicaoBloqueio {
        $bloqueio = EdicaoBloqueio::query()
            ->where(
                'recurso_tipo',
                $recursoTipo
            )
            ->where(
                'recurso_id',
                $recursoId
            )
            ->lockForUpdate()
            ->first();

        if (! $bloqueio) {
            throw new HttpException(
                423,
                'Este arquivo não possui uma sessão de edição ativa. Atualize a página antes de continuar.'
            );
        }

        return $bloqueio;
    }

    private function garantirPropriedade(
        EdicaoBloqueio $bloqueio,
        int $usuarioId,
        string $sessaoHash,
        string $token
    ): void {
        if ($bloqueio->expirou()) {
            throw new HttpException(
                423,
                'Sua sessão de edição expirou. Atualize a página para tentar adquirir o arquivo novamente.'
            );
        }

        if (
            ! $this->pertenceAoContexto(
                $bloqueio,
                $usuarioId,
                $sessaoHash
            )
            || ! hash_equals(
                (string) $bloqueio->token,
                $token
            )
        ) {
            throw new HttpException(
                423,
                $this->mensagemBloqueio(
                    $bloqueio
                )
            );
        }
    }

    private function pertenceAoContexto(
        EdicaoBloqueio $bloqueio,
        int $usuarioId,
        string $sessaoHash
    ): bool {
        return
            (int) $bloqueio->usuario_id === $usuarioId
            && hash_equals(
                (string) $bloqueio->sessao_hash,
                $sessaoHash
            );
    }

    private function normalizarRecursoTipo(
        string $recursoTipo
    ): string {
        $recursoTipo = Str::lower(
            trim($recursoTipo)
        );

        if (
            $recursoTipo === ''
            || mb_strlen($recursoTipo) > 80
            || ! preg_match(
                '/^[a-z0-9._-]+$/',
                $recursoTipo
            )
        ) {
            throw ValidationException::withMessages([
                'recurso_tipo' =>
                    'O tipo do arquivo informado para bloqueio é inválido.',
            ]);
        }

        return $recursoTipo;
    }

    private function validarIdentificadores(
        int $recursoId,
        int $usuarioId,
        string $sessaoId
    ): void {
        if ($recursoId <= 0) {
            throw ValidationException::withMessages([
                'recurso_id' =>
                    'O arquivo informado para bloqueio é inválido.',
            ]);
        }

        if ($usuarioId <= 0) {
            throw ValidationException::withMessages([
                'usuario_id' =>
                    'Não foi possível identificar o usuário da edição.',
            ]);
        }

        if (trim($sessaoId) === '') {
            throw ValidationException::withMessages([
                'sessao' =>
                    'Não foi possível identificar a sessão da edição.',
            ]);
        }
    }

    private function gerarHashSessao(
        string $sessaoId
    ): string {
        return hash(
            'sha256',
            $sessaoId
        );
    }
}