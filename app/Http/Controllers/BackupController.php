<?php

namespace App\Http\Controllers;

use App\Models\LogBackup;
use App\Services\Backup\Actions\DeleteBackupAction;
use App\Services\Backup\Actions\DownloadBackupAction;
use App\Services\Backup\Actions\GenerateBackupAction;
use App\Services\Backup\Actions\RestoreBackupAction;
use App\Services\Backup\Contracts\BackupDriverInterface;
use Illuminate\Http\Request;

class BackupController extends Controller
{
   public function index(BackupDriverInterface $driver)
{
    $disk = config('backup.disk', 'local');

    $arquivos = collect($driver->listar())
        ->map(function ($arquivo) use ($disk) {
            $nomeArquivo = basename($arquivo);

            $backupPath = 'backups/' . $nomeArquivo;

            $existe = \Illuminate\Support\Facades\Storage::disk($disk)->exists($backupPath);

            $tamanhoBytes = $existe
                ? \Illuminate\Support\Facades\Storage::disk($disk)->size($backupPath)
                : 0;

            $data = null;
            $hora = null;

            if (preg_match('/backup_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})\.zip$/', $nomeArquivo, $matches)) {
                $data = \Carbon\Carbon::createFromFormat('Y-m-d', $matches[1]);
                $hora = str_replace('-', ':', $matches[2]);
            }

            return [
                'nome' => $nomeArquivo,
                'data_raw' => $data,
                'data' => $data ? $data->format('d/m/Y') : '-',
                'hora' => $hora ?? '-',
                'tamanho' => $this->formatarBytes($tamanhoBytes),
                'bytes' => $tamanhoBytes,
                'valido' => $existe && $tamanhoBytes > 0,
            ];
        })
        ->sortByDesc('nome')
        ->values();

    $ultimoBackup = $arquivos->first();

    $statusBackup = [
        'classe' => 'danger',
        'icone' => 'bi-shield-x',
        'texto' => 'Sistema sem backup',
        'descricao' => 'Nenhum backup foi realizado até o momento.',
    ];

    if ($ultimoBackup && $ultimoBackup['data_raw']) {
        $dias = $ultimoBackup['data_raw']->diffInDays(now());

        if ($dias <= 1) {
            $statusBackup = [
                'classe' => 'success',
                'icone' => 'bi-shield-check',
                'texto' => 'Sistema protegido',
                'descricao' => 'Último backup realizado recentemente.',
            ];
            $dias = (int) round($dias);

        } elseif ($dias <= 7) {

            $diasInteiro = (int) floor((float) $dias);

            $statusBackup = [
                'classe' => 'warning',
                'icone' => 'bi-shield-exclamation',
                'texto' => 'Backup requer atenção',
                'descricao' => 'Último backup realizado há ' .
                    $diasInteiro . ' ' .
                    ($diasInteiro === 1 ? 'dia.' : 'dias.'),
            ];
        }
    }

    $resumo = [
        'ultimo_backup' => $ultimoBackup
            ? $ultimoBackup['data'] . ' às ' . $ultimoBackup['hora']
            : 'Nenhum',
        'total_backups' => $arquivos->count(),
        'espaco_total' => $this->formatarBytes($arquivos->sum('bytes')),
        'retencao' => config('backup.retention_days', 30) . ' dias',
        'driver' => ucfirst(config('backup.driver', 'local')),
        'destino' => 'storage/app/backups',
        'compressao' => 'ZIP',
    ];

    $logs = LogBackup::orderByDesc('id')
        ->limit(10)
        ->get();

    return view('backups.index', compact(
        'arquivos',
        'resumo',
        'logs',
        'statusBackup'
    ));
}

    public function gerar(GenerateBackupAction $action)
    {
        try {
            $arquivo = $action->execute();

            return redirect()
                ->route('backups.index')
                ->with('success', "Backup gerado com sucesso: {$arquivo}");

        } catch (\Throwable $e) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Erro ao gerar backup: ' . $e->getMessage());
        }
    }

    private function formatarBytes(int|float $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 2, ',', '.') . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 2, ',', '.') . ' MB';
        }

        return number_format($bytes / 1024 / 1024 / 1024, 2, ',', '.') . ' GB';
    }
    
    public function download(string $arquivo, DownloadBackupAction $action)
    {
        try {
            $path = $action->execute($arquivo);

            if (! file_exists($path)) {
                return redirect()
                    ->route('backups.index')
                    ->with('error', 'Arquivo de backup não encontrado.');
            }

            return response()->download($path);

        } catch (\Throwable $e) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Erro ao baixar backup: ' . $e->getMessage());
        }
    }

    public function destroy(string $arquivo, DeleteBackupAction $action)
    {
        try {
            $removido = $action->execute($arquivo);

            if (! $removido) {
                return redirect()
                    ->route('backups.index')
                    ->with('error', 'Backup não encontrado ou não pôde ser excluído.');
            }

            return redirect()
                ->route('backups.index')
                ->with('success', 'Backup excluído com sucesso.');

        } catch (\Throwable $e) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Erro ao excluir backup: ' . $e->getMessage());
        }
    }

    public function restaurar(Request $request, RestoreBackupAction $action)
    {
        $request->validate([
            'arquivo' => ['required', 'string'],
        ]);

        try {
            $action->execute($request->arquivo);

            return redirect()
                ->route('backups.index')
                ->with('success', 'Backup restaurado com sucesso.');

        } catch (\Throwable $e) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'Erro ao restaurar backup: ' . $e->getMessage());
        }
    }
}