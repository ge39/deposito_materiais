<?php

namespace App\Services\Expedicao;

use App\Models\Romaneio;
use App\Models\RomaneioEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class RomaneioEventoService
{
    public function registrar(
        Romaneio $romaneio,
        string $evento,
        ?string $etapa = null,
        ?string $statusAnterior = null,
        ?string $statusNovo = null,
        ?int $funcionarioId = null,
        ?string $observacao = null,
        array $dados = [],
        string $metodoIdentificacao = 'Sistema'
    ): RomaneioEvento {
        return RomaneioEvento::create([
            'romaneio_id' => $romaneio->id,
            'evento' => $evento,
            'etapa' => $etapa,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'metodo_identificacao' => $this->normalizarMetodoIdentificacao(
                $metodoIdentificacao
            ),
            'usuario_id' => $this->resolverUsuarioId(),
            'funcionario_id' => $funcionarioId,
            'terminal' => $this->resolverTerminal(),
            'endereco_ip' => $this->resolverEnderecoIp(),
            'observacao' => $observacao,
            'dados' => empty($dados) ? null : $dados,
            'ocorrido_em' => now(),
        ]);
    }

    public function registrarTransicao(
        Romaneio $romaneio,
        string $evento,
        string $etapa,
        string $statusAnterior,
        string $statusNovo,
        ?int $funcionarioId = null,
        ?string $observacao = null,
        array $dados = [],
        string $metodoIdentificacao = 'Sistema'
    ): RomaneioEvento {
        return $this->registrar(
            romaneio: $romaneio,
            evento: $evento,
            etapa: $etapa,
            statusAnterior: $statusAnterior,
            statusNovo: $statusNovo,
            funcionarioId: $funcionarioId,
            observacao: $observacao,
            dados: $dados,
            metodoIdentificacao: $metodoIdentificacao
        );
    }

    public function registrarCriacao(Romaneio $romaneio): RomaneioEvento
    {
        return $this->registrar(
            romaneio: $romaneio,
            evento: 'Romaneio criado',
            etapa: 'Montagem',
            statusNovo: $romaneio->status,
            dados: [
                'codigo_romaneio' => $romaneio->codigo_romaneio,
                'entrega_id' => $romaneio->entrega_id,
                'motorista_id' => $romaneio->motorista_id,
                'veiculo_id' => $romaneio->veiculo_id,
            ]
        );
    }

    public function registrarAbertura(
        Romaneio $romaneio,
        string $statusAnterior,
        string $metodoIdentificacao = 'codigo_barras'
    ): RomaneioEvento {
        return $this->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Operação aberta',
            etapa: 'Separacao',
            statusAnterior: $statusAnterior,
            statusNovo: $romaneio->status,
            dados: [
                'token_abertura_utilizado' => true,
            ],
            metodoIdentificacao: $metodoIdentificacao
        );
    }

    public function registrarRetornoEtapa(
        Romaneio $romaneio,
        string $etapa,
        string $statusAnterior,
        string $statusNovo,
        string $motivo
    ): RomaneioEvento {
        return $this->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Retorno de etapa',
            etapa: $etapa,
            statusAnterior: $statusAnterior,
            statusNovo: $statusNovo,
            observacao: $motivo,
            dados: [
                'motivo_retorno' => $motivo,
            ]
        );
    }

    public function registrarCancelamento(
        Romaneio $romaneio,
        string $statusAnterior,
        string $motivo
    ): RomaneioEvento {
        return $this->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Romaneio cancelado',
            etapa: 'Cancelamento',
            statusAnterior: $statusAnterior,
            statusNovo: 'Cancelado',
            observacao: $motivo,
            dados: [
                'motivo_cancelamento' => $motivo,
            ]
        );
    }

    public function registrarFechamento(
        Romaneio $romaneio,
        string $statusAnterior,
        string $metodoIdentificacao,
        ?string $justificativaManual = null
    ): RomaneioEvento {
        return $this->registrarTransicao(
            romaneio: $romaneio,
            evento: 'Romaneio fechado',
            etapa: 'Fechamento',
            statusAnterior: $statusAnterior,
            statusNovo: 'Fechado',
            observacao: $justificativaManual,
            dados: [
                'metodo_fechamento' => $metodoIdentificacao,
                'justificativa_manual' => $justificativaManual,
            ],
            metodoIdentificacao: $metodoIdentificacao
        );
    }

    private function resolverUsuarioId(): int
    {
        $usuarioId = Auth::id();

        if (! $usuarioId) {
            throw new RuntimeException(
                'Não foi possível identificar o usuário autenticado para registrar o evento do romaneio.'
            );
        }

        return (int) $usuarioId;
    }

    private function resolverTerminal(): ?string
    {
        $request = $this->requestAtual();

        if (! $request) {
            return null;
        }

        $terminalInformado = trim(
            (string) $request->header('X-Terminal-Name', '')
        );

        if ($terminalInformado !== '') {
            return mb_substr($terminalInformado, 0, 120);
        }

        $userAgent = trim((string) $request->userAgent());

        return $userAgent !== ''
            ? mb_substr($userAgent, 0, 120)
            : null;
    }

    private function resolverEnderecoIp(): ?string
    {
        $request = $this->requestAtual();

        if (! $request) {
            return null;
        }

        $ip = trim((string) $request->ip());

        return $ip !== ''
            ? mb_substr($ip, 0, 45)
            : null;
    }

    private function requestAtual(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request
            ? $request
            : null;
    }

    private function normalizarMetodoIdentificacao(string $metodo): string
    {
        $metodo = strtolower(trim($metodo));

        return match ($metodo) {
            'codigo_barras',
            'código_barras',
            'barcode' => 'Codigo_barras',

            'qr_code',
            'qrcode',
            'qr' => 'Qr_code',

            'codigo_operacional',
            'código_operacional',
            'codigo' => 'Codigo_operacional',

            'pesquisa_manual',
            'manual',
            'busca_manual' => 'Pesquisa_manual',

            default => 'Sistema',
        };
    }
}