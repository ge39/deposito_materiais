<?php

namespace App\Http\Controllers;

use App\Models\LogBackup;
use App\Services\Backup\BackupAutomationSettings;
use Illuminate\Http\Request;

class BackupAdministracaoController extends Controller
{
    public function automatico(BackupAutomationSettings $settings)
    {
        return view('backups-admin.automatico', [
            'configuracao' => $settings->obter(),
            'horarios' => $settings->horarios(),
        ]);
    }

    public function salvarAutomatico(
        Request $request,
        BackupAutomationSettings $settings
    ) {
        $dados = $request->validate([
            'ativo' => 'nullable|boolean',
            'quantidade' => 'required|integer|min:1|max:5',
            'horarios' => 'required|array|min:1|max:5',
            'horarios.*' => 'required|date_format:H:i',
        ]);

        $quantidade = (int) $dados['quantidade'];

        $horarios = array_slice(
            array_values($dados['horarios']),
            0,
            $quantidade
        );

        if (count($horarios) !== $quantidade) {
            return back()->withInput()->withErrors([
                'horarios' => 'Informe todos os horarios.'
            ]);
        }

        if (count(array_unique($horarios)) !== $quantidade) {
            return back()->withInput()->withErrors([
                'horarios' => 'Os horarios nao podem se repetir.'
            ]);
        }

        $atual = $settings->obter();

        $settings->salvar([
            'ativo' => (bool) ($dados['ativo'] ?? false),
            'quantidade' => $quantidade,
            'horarios_personalizados' => $horarios,
            'destino_secundario' => $atual['destino_secundario'],
        ]);

        return redirect()
            ->route('backups.automatico')
            ->with('success', 'Agendamento salvo com sucesso.');
    }

    public function configuracao(BackupAutomationSettings $settings)
    {
        return view('backups-admin.configuracao', [
            'configuracao' => $settings->obter(),
            'driver' => config('backup.driver', 'local'),
            'disk' => config('backup.disk', 'local'),
            'retencao' => config('backup.retention_days', 30),
        ]);
    }

    public function salvarConfiguracao(
        Request $request,
        BackupAutomationSettings $settings
    ) {
        $dados = $request->validate([
            'destino_secundario' => 'nullable|string|max:500',
        ]);

        $destino = trim(
            (string) ($dados['destino_secundario'] ?? '')
        );

        if ($destino !== '') {
            $real = realpath($destino);

            if ($real === false || !is_dir($real)) {
                return back()->withInput()->withErrors([
                    'destino_secundario' =>
                        'Selecione um diretorio existente no servidor.'
                ]);
            }

            $destino = $real;
        }

        $atual = $settings->obter();
        $atual['destino_secundario'] = $destino;

        $settings->salvar([
            'ativo' => $atual['ativo'],
            'quantidade' => count($settings->horarios()),
            'horarios_personalizados' => $settings->horarios(),
            'destino_secundario' => $destino,
        ]);

        return redirect()
            ->route('backups.configuracao')
            ->with('success', 'Configuracao salva com sucesso.');
    }

    public function explorarDiretorios(Request $request)
    {
        $caminho = trim((string) $request->query('path', ''));

        if ($caminho === '') {
            $unidades = [];

            if (PHP_OS_FAMILY === 'Windows') {
                foreach (range('A', 'Z') as $letra) {
                    $unidade = $letra . ':\\';

                    if (is_dir($unidade)) {
                        $unidades[] = [
                            'nome' => $letra . ':',
                            'caminho' => $unidade,
                        ];
                    }
                }
            } else {
                $unidades[] = [
                    'nome' => '/',
                    'caminho' => '/',
                ];
            }

            return response()->json([
                'atual' => '',
                'pai' => '',
                'pastas' => $unidades,
            ]);
        }

        // Navegacao somente por caminhos absolutos.
        $absoluto = PHP_OS_FAMILY === 'Windows'
            ? (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $caminho)
            : str_starts_with($caminho, '/');

        if (!$absoluto || str_contains($caminho, "\0")) {
            return response()->json([
                'message' => 'Caminho invalido.'
            ], 422);
        }

        $real = realpath($caminho);

        if ($real === false || !is_dir($real)) {
            return response()->json([
                'message' => 'Diretorio indisponivel.'
            ], 404);
        }

        if (!is_readable($real)) {
            return response()->json([
                'message' => 'Sem permissao para consultar a pasta.'
            ], 403);
        }

        $itens = @scandir($real);

        if ($itens === false) {
            return response()->json([
                'message' => 'Nao foi possivel listar a pasta.'
            ], 403);
        }

        $pastas = [];

        foreach ($itens as $nome) {
            if ($nome === '.' || $nome === '..') {
                continue;
            }

            $filho = $real . DIRECTORY_SEPARATOR . $nome;

            if (is_dir($filho) && !is_link($filho)) {
                $pastas[] = [
                    'nome' => $nome,
                    'caminho' => $filho,
                ];
            }
        }

        usort(
            $pastas,
            fn ($a, $b) => strnatcasecmp($a['nome'], $b['nome'])
        );

        $pai = dirname($real);

        if ($pai === $real) {
            $pai = '';
        }

        return response()->json([
            'atual' => $real,
            'pai' => $pai,
            'pastas' => $pastas,
        ]);
    }

    public function historico(
        \App\Services\Backup\BackupHistoricoConsultaService $consulta
    ) {
        $logs = LogBackup::orderByDesc('id')->paginate(30);

        return view('backups-admin.historico', [
            'logs' => $consulta->preparar($logs),
        ]);
    }
}