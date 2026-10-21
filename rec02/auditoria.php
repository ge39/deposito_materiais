<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

set_time_limit(180);

function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function bytes(int $n): string {
    if ($n >= 1073741824) {
        return number_format($n / 1073741824, 2, ',', '.') . ' GB';
    }
    return number_format($n / 1048576, 2, ',', '.') . ' MB';
}

function localizarBackups(): array {
    $raizes = [
        'Principal' =>
            'C:/xampp/htdocs/deposito_materiais/storage',
        'Local adicional' =>
            'C:/xampp/htdocs/deposito_materiais/backups',
        'Secundario' =>
            'G:/Meu Drive/backup/deposito_materiais'
    ];

    $resultado = [];
    $vistos = [];

    foreach ($raizes as $origem => $raiz) {
        if (!is_dir($raiz)) {
            continue;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $raiz,
                    FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {
                if ($iterator->getDepth() > 8) {
                    continue;
                }

                if (!$file->isFile() ||
                    strtolower($file->getExtension()) !== 'zip') {
                    continue;
                }

                $path = realpath($file->getPathname());

                if ($path === false) {
                    continue;
                }

                $chave = strtolower(str_replace('\\', '/', $path));

                if (isset($vistos[$chave])) {
                    continue;
                }

                $vistos[$chave] = true;

                $id = hash('sha256', $chave);

                $resultado[$id] = [
                    'id' => $id,
                    'nome' => $file->getFilename(),
                    'origem' => $origem,
                    'path' => $path,
                    'tamanho' => $file->getSize(),
                    'data' => $file->getMTime()
                ];

                if (count($resultado) >= 1000) {
                    break 2;
                }
            }
        } catch (Throwable $e) {
            continue;
        }
    }

    uasort($resultado, fn($a, $b) => $b['data'] <=> $a['data']);

    return $resultado;
}

function analisarSQL($stream): array {
    $tabelas = [];
    $inserts = [];
    $buffer = '';
    $lido = 0;
    $limite = 512 * 1048576;
    $erro = '';

    while (!feof($stream)) {
        $parte = fread($stream, 65536);

        if ($parte === false) {
            $erro = 'Falha na leitura do SQL.';
            break;
        }

        if ($parte === '') {
            break;
        }

        $lido += strlen($parte);

        if ($lido > $limite) {
            $erro = 'SQL excedeu o limite de analise de 512 MB.';
            break;
        }

        $buffer .= $parte;

        // Procura instrucoes SQL sem carregar o dump inteiro.
        // A analise e indicativa, nao um parser SQL completo.
        if (preg_match_all(
            '/\bCREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:`?[\w]+`?\s*\.\s*)?`?([a-zA-Z0-9_]+)`?/i',
            $buffer,
            $matches
        )) {
            foreach ($matches[1] as $nome) {
                $tabelas[strtolower($nome)] = true;
            }
        }

        if (preg_match_all(
            '/\b(?:INSERT|REPLACE)\s+INTO\s+(?:`?[\w]+`?\s*\.\s*)?`?([a-zA-Z0-9_]+)`?/i',
            $buffer,
            $matches
        )) {
            foreach ($matches[1] as $nome) {
                $inserts[strtolower($nome)] = true;
            }
        }

        // Mantem sobreposicao para instrucoes entre blocos.
        $buffer = substr($buffer, -1024);
    }

    ksort($tabelas);
    ksort($inserts);

    return [
        'tabelas' => array_keys($tabelas),
        'inserts' => array_keys($inserts),
        'bytes' => $lido,
        'erro' => $erro
    ];
}

function auditarZip(array $arquivo): array {
    $r = [
        'sha256' => '',
        'status' => 'NAO VALIDADO',
        'erros' => [],
        'sql' => [],
        'arquivos' => 0,
        'integridade' => 'NAO VERIFICADA',
        'verificados' => 0
    ];

    if (!class_exists('ZipArchive')) {
        $r['erros'][] = 'Extensao ZIP nao disponivel.';
        return $r;
    }

    $hash = hash_file('sha256', $arquivo['path']);

    if ($hash === false) {
        $r['erros'][] = 'Falha no calculo SHA-256.';
        return $r;
    }

    $r['sha256'] = $hash;

    $zip = new ZipArchive();

    if ($zip->open($arquivo['path'], ZipArchive::RDONLY) !== true) {
        $r['erros'][] = 'Falha ao abrir ZIP.';
        return $r;
    }

    try {
        $r['arquivos'] = $zip->numFiles;
        $limiteEntradas = 10000;
        $limiteEntradaBytes = 1024 * 1048576;
        $limiteTotalBytes = 2 * 1073741824;
        $totalLido = 0;
        $completo = ($zip->numFiles <= $limiteEntradas);

        for ($i = 0; $i < min($zip->numFiles, $limiteEntradas); $i++) {
            $stat = $zip->statIndex($i);

            if ($stat === false) {
                $r['erros'][] = "Erro ao consultar entrada $i.";
                $completo = false;
                continue;
            }

            $nome = $stat['name'];

            if (str_ends_with($nome, '/')) {
                continue;
            }

            if ($stat['size'] > $limiteEntradaBytes) {
                $r['erros'][] = "Entrada excede 1 GB: $nome";
                $completo = false;
                continue;
            }

            $stream = $zip->getStream($nome);

            if ($stream === false) {
                $r['erros'][] = "Falha ao abrir entrada: $nome";
                $completo = false;
                continue;
            }

            $context = hash_init('crc32b');
            $lido = 0;
            $sql = '';
            $sqlDados = null;

            if (preg_match('/\.sql$/i', $nome)) {
                $sql = $nome;
            }

            // A leitura ocorre em fluxo, sem extrair arquivos.
            // Para SQL, analisamos apenas o inicio limitado do fluxo.
            // A verificacao CRC abrange a entrada inteira.
            $bufferSQL = '';

            while (!feof($stream)) {
                $chunk = fread($stream, 65536);

                if ($chunk === false) {
                    $completo = false;
                    $r['erros'][] = "Falha na leitura: $nome";
                    break;
                }

                if ($chunk === '') {
                    if (!feof($stream)) {
                        $completo = false;
                        $r['erros'][] = "Leitura interrompida: $nome";
                    }
                    break;
                }

                $lido += strlen($chunk);
                $totalLido += strlen($chunk);

                hash_update($context, $chunk);

                if ($sql !== '' && strlen($bufferSQL) < 32 * 1048576) {
                    $restante = 32 * 1048576 - strlen($bufferSQL);
                    $bufferSQL .= substr($chunk, 0, $restante);
                }

                if ($lido > $limiteEntradaBytes ||
                    $totalLido > $limiteTotalBytes) {
                    $completo = false;
                    $r['erros'][] = 'Limite de leitura atingido.';
                    break;
                }
            }

            fclose($stream);

            if ($lido !== (int)$stat['size']) {
                $completo = false;
                $r['erros'][] = "Tamanho divergente: $nome";
            } else {
                $crcReal = strtolower(hash_final($context));
                $crcZip = sprintf('%08x', $stat['crc']);

                if ($crcReal !== strtolower($crcZip)) {
                    $completo = false;
                    $r['erros'][] = "CRC divergente: $nome";
                } else {
                    $r['verificados']++;
                }
            }

            if ($sql !== '') {
                $temp = fopen('php://temp', 'w+');

                if ($temp !== false) {
                    fwrite($temp, $bufferSQL);
                    rewind($temp);
                    $sqlDados = analisarSQL($temp);
                    fclose($temp);
                }

                $r['sql'][] = [
                    'nome' => $sql,
                    'bytes' => $stat['size'],
                    'analise' => $sqlDados,
                    'parcial' => $stat['size'] > 32 * 1048576
                ];
            }

            if ($totalLido > $limiteTotalBytes) {
                break;
            }
        }

        if ($completo && !$r['erros']) {
            $r['integridade'] = 'CRC OK';
            $r['status'] = 'ZIP INTEGRO';
        } else {
            $r['integridade'] = 'INCOMPLETA OU COM ERROS';
            $r['status'] = 'REQUER ANALISE';
        }

    } finally {
        $zip->close();
    }

    return $r;
}

$backups = localizarBackups();
$id = $_GET['id'] ?? '';

if (!is_string($id)) {
    $id = '';
}

$selecionado = $backups[$id] ?? null;
$auditoria = null;
$comparacoes = [];

if ($selecionado) {
    $auditoria = auditarZip($selecionado);

    foreach ($backups as $item) {
        if ($item['nome'] !== $selecionado['nome']) {
            continue;
        }

        if ($item['id'] === $selecionado['id']) {
            continue;
        }

        $hashOutro = hash_file('sha256', $item['path']);

        $comparacoes[] = [
            'origem' => $item['origem'],
            'path' => $item['path'],
            'igual' => $hashOutro !== false &&
                hash_equals($auditoria['sha256'], $hashOutro)
        ];
    }
}

$essenciais = [
    'users',
    'clientes',
    'produtos',
    'orcamentos',
    'movimentacoes_caixa'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>REC-02 V2 | Auditoria</title>

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    background: #101827;
    color: #e5e7eb;
    font-family: Segoe UI, Arial, sans-serif;
}
header {
    background: #19283f;
    padding: 24px 5%;
    border-bottom: 1px solid #3a4c65;
}
h1 { margin: 0 0 8px; }
h2 { font-size: 20px; }
main {
    max-width: 1400px;
    margin: auto;
    padding: 24px;
}
section {
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #34445c;
    border-radius: 12px;
    background: #1b2940;
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #34445c;
    text-align: left;
}
th { color: #a6c9f4; }
a { color: #7ccaff; }
.ok { color: #68dfaa; }
.warn { color: #ffcd7c; }
.error { color: #ff9090; }
.muted { color: #adbed4; }
.path { overflow-wrap: anywhere; }
.btn {
    display: inline-block;
    background: #3476c8;
    color: white;
    padding: 10px 16px;
    border-radius: 7px;
    text-decoration: none;
}
.notice {
    padding: 15px;
    background: #303524;
    border: 1px solid #6e7146;
    border-radius: 8px;
    margin-bottom: 20px;
}
code {
    overflow-wrap: anywhere;
}
</style>
</head>
<body>

<header>
<h1>REC-02 V2 — Auditoria de Backups</h1>
<div class="muted">
Deposito Materiais | Recuperacao independente
</div>
</header>

<main>

<p>
<a href="index.php">Voltar a Central REC-02</a>
</p>

<div class="notice">
Esta auditoria nao executa restauracoes, nao modifica
arquivos SQL e nao altera o MariaDB.
</div>

<section>
<h2>Inventario de backups</h2>
<p class="muted">
<?= count($backups) ?> arquivos ZIP identificados.
Selecione um arquivo para executar a validacao.
</p>

<table>
<thead>
<tr>
<th>Backup</th>
<th>Origem</th>
<th>Tamanho</th>
<th>Data</th>
<th>Acao</th>
</tr>
</thead>
<tbody>

<?php foreach ($backups as $b): ?>
<tr>
<td><?= h($b['nome']) ?></td>
<td><?= h($b['origem']) ?></td>
<td><?= h(bytes($b['tamanho'])) ?></td>
<td><?= date('d/m/Y H:i:s', $b['data']) ?></td>
<td>
<a href="?id=<?= h($b['id']) ?>#resultado">
Auditar
</a>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</section>

<?php if ($selecionado && $auditoria): ?>

<section id="resultado">
<h2>Resultado da auditoria</h2>

<p><strong>Arquivo:</strong> <?= h($selecionado['nome']) ?></p>
<p class="path"><strong>Caminho:</strong>
<?= h($selecionado['path']) ?></p>

<p><strong>Status:</strong>
<span class="<?= $auditoria['status'] === 'ZIP INTEGRO' ? 'ok' : 'warn' ?>">
<?= h($auditoria['status']) ?>
</span>
</p>

<p><strong>SHA-256:</strong>
<code><?= h($auditoria['sha256']) ?></code></p>

<p><strong>Integridade:</strong>
<?= h($auditoria['integridade']) ?></p>

<p><strong>Entradas:</strong>
<?= $auditoria['arquivos'] ?></p>

<p><strong>Entradas com CRC validado:</strong>
<?= $auditoria['verificados'] ?></p>

<?php foreach ($auditoria['erros'] as $erro): ?>
<p class="error"><?= h($erro) ?></p>
<?php endforeach; ?>

</section>

<section>
<h2>Comparacao entre copias</h2>

<table>
<thead>
<tr><th>Origem</th><th>Caminho</th><th>SHA-256</th></tr>
</thead>
<tbody>
<?php foreach ($comparacoes as $c): ?>
<tr>
<td><?= h($c['origem']) ?></td>
<td class="path"><?= h($c['path']) ?></td>
<td class="<?= $c['igual'] ? 'ok' : 'error' ?>">
<?= $c['igual'] ? 'IDENTICO' : 'DIFERENTE / FALHA' ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$comparacoes): ?>
<tr>
<td colspan="3">Nenhuma outra copia com o mesmo nome foi localizada.</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</section>

<section>
<h2>Auditoria do banco SQL</h2>

<?php if (!$auditoria['sql']): ?>
<p class="warn">Nenhum arquivo SQL foi identificado no ZIP.</p>
<?php endif; ?>

<?php foreach ($auditoria['sql'] as $sql): ?>

<h3><?= h($sql['nome']) ?></h3>

<p>Tamanho: <?= h(bytes((int)$sql['bytes'])) ?></p>

<?php
$analise = $sql['analise'];
$tabelas = $analise['tabelas'] ?? [];
$inserts = $analise['inserts'] ?? [];
?>

<p>
Tabelas com CREATE TABLE identificado:
<strong><?= count($tabelas) ?></strong>
</p>

<p>
Tabelas com INSERT/REPLACE identificado:
<strong><?= count($inserts) ?></strong>
</p>

<?php if ($sql['parcial']): ?>
<p class="warn">
Analise SQL limitada aos primeiros 32 MB.
A contagem pode ser parcial.
</p>
<?php endif; ?>

<?php if (!empty($analise['erro'])): ?>
<p class="warn"><?= h($analise['erro']) ?></p>
<?php endif; ?>

<h3>Tabelas essenciais do ERP</h3>

<table>
<thead>
<tr>
<th>Tabela</th>
<th>CREATE TABLE</th>
<th>INSERT/REPLACE</th>
</tr>
</thead>
<tbody>

<?php foreach ($essenciais as $nome): ?>
<tr>
<td><?= h($nome) ?></td>
<td class="<?= in_array($nome, $tabelas, true) ? 'ok' : 'warn' ?>">
<?= in_array($nome, $tabelas, true) ? 'Encontrado' : 'Nao identificado' ?>
</td>
<td class="<?= in_array($nome, $inserts, true) ? 'ok' : 'warn' ?>">
<?= in_array($nome, $inserts, true) ? 'Encontrado' : 'Nao identificado' ?>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<h3>Lista das tabelas identificadas</h3>
<p class="muted">
<?= h(implode(', ', $tabelas)) ?>
</p>

<?php endforeach; ?>

</section>

<?php endif; ?>

</main>
</body>
</html>