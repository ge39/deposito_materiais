<?php

namespace App\Services\Logging;

use App\Models\LogBackup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class BackupLogService
{
    public function iniciar(
        string $acao,
        ?string $arquivo = null,
        array $metadata = []
    ): LogBackup {
        return LogBackup::create([
            'user_id' => Auth::id(),
            'acao' => $acao,
            'arquivo' => $arquivo,
            'driver' => config('backup.driver', 'local'),
            'status' => 'pendente',
            'metadata' => $this->montarMetadata($metadata),
            'iniciado_em' => now(),
        ]);
    }

    public function sucesso(
        LogBackup $log,
        ?string $arquivo = null,
        int $tamanhoBytes = 0,
        ?string $mensagem = null,
        array $metadata = []
    ): LogBackup {
        $metadataFinal = array_merge(
            is_array($log->metadata) ? $log->metadata : [],
            $this->montarMetadata($metadata)
        );

        $log->status = 'sucesso';
        $log->arquivo = $arquivo ?? $log->arquivo;
        $log->tamanho_bytes = $tamanhoBytes;
        $log->mensagem = $this->limparTexto($mensagem);
        $log->metadata = $metadataFinal;
        $log->finalizado_em = now();
        $log->duracao_ms = $this->calcularDuracao($log);

        $log->save();

        return $log;
    }

    public function erro(
        LogBackup $log,
        \Throwable $exception,
        array $metadata = []
    ): LogBackup {
        $metadataFinal = array_merge(
            is_array($log->metadata) ? $log->metadata : [],
            $this->montarMetadata(array_merge($metadata, [
                'erro_classe' => get_class($exception),
            ]))
        );

        $log->status = 'erro';
        $log->mensagem = $this->limparTexto($exception->getMessage());
        $log->metadata = $metadataFinal;
        $log->finalizado_em = now();
        $log->duracao_ms = $this->calcularDuracao($log);

        $log->save();

        return $log;
    }

    private function calcularDuracao(LogBackup $log): ?int
    {
        if (! $log->iniciado_em) {
            return null;
        }

        return (int) $log->iniciado_em->diffInMilliseconds(now());
    }

    private function montarMetadata(array $metadata = []): array
    {
        return array_merge([
            'driver' => config('backup.driver', 'local'),
            'disk' => config('backup.disk', 'local'),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'host' => gethostname(),
            'ambiente' => app()->environment(),
            'executado_em' => now()->toDateTimeString(),
        ], $metadata);
    }

    private function limparTexto(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }

        $texto = mb_convert_encoding(
            $texto,
            'UTF-8',
            'UTF-8, ISO-8859-1, Windows-1252, CP850'
        );

        return mb_substr($texto, 0, 5000);
    }
}