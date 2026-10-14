<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecuperacaoDiagnosticoController extends Controller
{
    // REC01_DETECTOR_V4
    // Inventario dinamico e consultas somente leitura.

    public function index(Request $request)
    {
        $usuario = $request->user();

        abort_unless(
            $usuario &&
            (bool) $usuario->ativo &&
            $usuario->nivel_acesso === 'admin',
            403
        );

        $resultado = [
            'banco' => null,
            'totalEsperadas' => 0,
            'totalPresentes' => 0,
            'totalAusentes' => 0,
            'falhas' => [],
            'backups' => [],
            'concluido' => false,
            'erro' => null,
            'inventarioLimitado' => true,
        ];

        try {
            $banco = DB::connection()->getDatabaseName();

            $resultado['banco'] = $banco;

            $inventario = $this->inventariarTabelas();

            $resultado['totalEsperadas'] = count($inventario);

            $tabelasBanco = DB::select(
                "SELECT TABLE_NAME
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_TYPE IN ('BASE TABLE', 'VIEW')",
                [$banco]
            );

            $existentes = [];

            foreach ($tabelasBanco as $item) {
                $existentes[$item->TABLE_NAME] = true;
            }

            foreach ($inventario as $nome => $origens) {

                if (isset($existentes[$nome])) {
                    $resultado['totalPresentes']++;
                    continue;
                }

                $similares = $this->procurarSimilares(
                    $nome,
                    array_keys($existentes)
                );

                // Relacionamentos associados aos nomes
                // encontrados. Nao comprovam equivalencia.
                $dependencias = DB::select(
                    "SELECT TABLE_NAME,
                            COLUMN_NAME,
                            REFERENCED_TABLE_NAME,
                            REFERENCED_COLUMN_NAME
                     FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = ?
                       AND REFERENCED_TABLE_NAME IN
                       (" . implode(',', array_fill(0, count($similares) + 1, '?')) . ")
                     ORDER BY TABLE_NAME, COLUMN_NAME",
                    array_merge([$banco, $nome], $similares)
                );

                // REC01_CLASSIFICACAO_V5
                $evidencia = $this->verificarErroOperacional($nome);

                $classificacao = $evidencia
                    ? 'FALHA_CONFIRMADA'
                    : 'REQUER_INVESTIGACAO';
                $resultado['falhas'][] = [
                    'tabela' => $nome,
                    'classificacao' => $classificacao,
                    'evidencia' => $evidencia,
                    'modulo' => $this->identificarModulo($origens),
                    'origens' => $origens,
                    'similares' => $similares,
                    'dependencias' => $dependencias,
                ];
            }

            usort($resultado['falhas'], function ($a, $b) {
                return strcmp($a['tabela'], $b['tabela']);
            });

            $resultado['totalAusentes'] = count($resultado['falhas']);
            $resultado['concluido'] = true;

        } catch (Throwable $e) {

            $resultado['erro'] =
                'Não foi possível concluir a verificação do banco.';

            Log::error('REC01_V4_DIAGNOSTICO_FALHOU', [
                'tipo' => get_class($e),
                'mensagem' => $e->getMessage(),
            ]);
        }

        $resultado['backups'] = $this->listarBackups();

        return response()
            ->view('admin.recuperacao.painel', $resultado)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY');
    }

    private function inventariarTabelas(): array
    {
        $inventario = [];

        $diretorios = [
            app_path('Models'),
            app_path('Http/Controllers'),
            app_path('Services'),
            app_path('Providers'),
        ];

        foreach ($diretorios as $diretorio) {

            if (!is_dir($diretorio)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $diretorio,
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $arquivo) {

                if (
                    !$arquivo->isFile() ||
                    strtolower($arquivo->getExtension()) !== 'php'
                ) {
                    continue;
                }

                $conteudo = file_get_contents(
                    $arquivo->getPathname()
                );

                if ($conteudo === false) {
                    continue;
                }

                $relativo = str_replace(
                    '\\',
                    '/',
                    substr(
                        $arquivo->getPathname(),
                        strlen(base_path()) + 1
                    )
                );

                // Exclui comentarios PHP antes da pesquisa.
                $tokens = token_get_all($conteudo);
                $codigo = '';

                foreach ($tokens as $token) {

                    if (is_array($token)) {
                        if (in_array(
                            $token[0],
                            [T_COMMENT, T_DOC_COMMENT],
                            true
                        )) {
                            $codigo .= ' ';
                            continue;
                        }

                        $codigo .= $token[1];
                    } else {
                        $codigo .= $token;
                    }
                }

                $padroes = [
                    // Models com tabela explicitamente declarada.
                    '/(?:protected|public)\s+\$table\s*=\s*["\x27]([a-zA-Z_][a-zA-Z0-9_]*)["\x27]/',

                    // Consultas pelo Query Builder.
                    '/\bDB::table\s*\(\s*["\x27]([a-zA-Z_][a-zA-Z0-9_]*)["\x27]/',
                    '/\bDB::(?:connection\s*\([^)]*\)\s*->\s*)?table\s*\(\s*["\x27]([a-zA-Z_][a-zA-Z0-9_]*)["\x27]/',
                    '/->(?:from|join|leftJoin|rightJoin|crossJoin)\s*\(\s*["\x27]([a-zA-Z_][a-zA-Z0-9_]*)["\x27]/',

                    // SQL literal em strings PHP.
                    '/\b(?:FROM|JOIN|UPDATE|INTO)\s+[`]?([a-zA-Z_][a-zA-Z0-9_]*)[`]?(?=\s|[,;)])/i',
                ];

                foreach ($padroes as $padrao) {

                    if (!preg_match_all($padrao, $codigo, $matches)) {
                        continue;
                    }

                    foreach ($matches[1] as $tabela) {

                        $tabela = strtolower($tabela);

                        if (
                            strlen($tabela) < 3 ||
                            in_array(
                                $tabela,
                                ['information_schema', 'dual'],
                                true
                            )
                        ) {
                            continue;
                        }

                        $inventario[$tabela][$relativo] = true;
                    }
                }
            }
        }

        $resultado = [];

        foreach ($inventario as $tabela => $origens) {
            $resultado[$tabela] = array_keys($origens);
        }

        ksort($resultado);

        return $resultado;
    }

    /**
     * REC01_CLASSIFICACAO_V5
     *
     * Procura evidencias de erro MySQL 1146 recente.
     * Leitura limitada ao log Laravel.
     * Nao modifica arquivos ou banco de dados.
     */
    private function verificarErroOperacional(string $tabela): bool
    {
        $arquivo = storage_path('logs/laravel.log');

        if (!is_file($arquivo) || !is_readable($arquivo)) {
            return false;
        }

        $handle = fopen($arquivo, 'rb');

        if ($handle === false) {
            return false;
        }

        try {
            $tamanho = filesize($arquivo);

            if ($tamanho === false || $tamanho <= 0) {
                return false;
            }

            // Leitura limitada aos ultimos 2 MB.
            $limite = 2 * 1024 * 1024;
            $inicio = max(0, $tamanho - $limite);

            fseek($handle, $inicio);

            $conteudo = stream_get_contents($handle);

            if ($conteudo === false) {
                return false;
            }

            // Considera apenas registros dos ultimos 7 dias.
            $dataLimite = now()->subDays(7)->format('Y-m-d');

            $padrao = '/\[(\d{4}-\d{2}-\d{2})[^\]]*\].*?'
                . 'SQLSTATE\[42S02\].*?'
                . '(?:1146).*?'
                . 'Table\s+[\x27\x60]?'
                . '[a-zA-Z0-9_]+\.'
                . preg_quote($tabela, '/')
                . '[\x27\x60]?\s+doesn\x27t exist/is';

            if (!preg_match_all($padrao, $conteudo, $matches, PREG_SET_ORDER)) {
                return false;
            }

            foreach ($matches as $match) {
                if ($match[1] >= $dataLimite) {
                    return true;
                }
            }

            return false;

        } finally {
            fclose($handle);
        }
    }
    private function procurarSimilares(
        string $esperada,
        array $existentes
    ): array {

        $resultado = [];

        foreach ($existentes as $nome) {

            $distancia = levenshtein(
                strtolower($esperada),
                strtolower($nome)
            );

            if (
                $distancia <= 3 ||
                str_starts_with($nome, $esperada) ||
                str_starts_with($esperada, $nome)
            ) {
                $resultado[] = $nome;
            }
        }

        usort($resultado, function ($a, $b) use ($esperada) {
            return levenshtein($esperada, $a)
                <=> levenshtein($esperada, $b);
        });

        return array_slice($resultado, 0, 5);
    }

    private function identificarModulo(array $origens): string
    {
        $texto = strtolower(implode(' ', $origens));

        $mapa = [
            'caixa' => 'Financeiro / Caixa',
            'pdv' => 'Comercial / PDV',
            'orcamento' => 'Comercial / Orçamentos',
            'estoque' => 'Estoque',
            'produto' => 'Estoque / Produtos',
            'venda' => 'Comercial / Vendas',
            'entrega' => 'Logística / Entregas',
            'romaneio' => 'Logística / Romaneios',
            'cliente' => 'Comercial / Clientes',
            'compra' => 'Estoque / Compras',
            'funcionario' => 'Pessoas',
        ];

        foreach ($mapa as $termo => $modulo) {
            if (str_contains($texto, $termo)) {
                return $modulo;
            }
        }

        return 'Módulo não determinado';
    }

    private function listarBackups(): array
    {
        $diretorio = storage_path('app/private/backups');

        if (!is_dir($diretorio)) {
            return [];
        }

        $arquivos = glob(
            $diretorio . DIRECTORY_SEPARATOR . 'backup_*.zip'
        ) ?: [];

        $arquivos = array_filter(
            $arquivos,
            fn ($arquivo) =>
                is_file($arquivo) &&
                preg_match(
                    '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.zip$/',
                    basename($arquivo)
                )
        );

        rsort($arquivos, SORT_STRING);

        $resultado = [];

        foreach (array_slice($arquivos, 0, 10) as $arquivo) {
            $resultado[] = [
                'nome' => basename($arquivo),
                'tamanho' => filesize($arquivo),
                'data' => filemtime($arquivo),
            ];
        }

        return $resultado;
    }
}