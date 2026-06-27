<?php

namespace App\Services\Backup\Contracts;

interface BackupDriverInterface
{
    public function salvar(string $arquivoLocal): string;

    public function listar(): array;

    public function baixar(string $nomeArquivo): string;

    public function excluir(string $nomeArquivo): bool;
}