<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function tamanho(int $bytes): string {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
    }
    return number_format($bytes / 1024, 1, ',', '.') . ' KB';
}

$raizes = [
    'ERP - storage' => 'C:/xampp/htdocs/deposito_materiais/storage',
    'ERP - backups' => 'C:/xampp/htdocs/deposito_materiais/backups',
    'XAMPP - backups ERP' => 'C:/xampp/backup/deposito_materiais',
    'Google Drive - secundario' => 'G:/Meu Drive/backup/deposito_materiais'
];

$arquivos = [];
$pastas = [];
$contador = 0;

foreach ($raizes as $origem => $raiz) {
    if (!is_dir($raiz)) {
        $pastas[$origem] = 'Nao encontrada';
        continue;
    }

    $pastas[$origem] = 'Encontrada';

    try {
        $diretorio = new RecursiveDirectoryIterator(
            $raiz,
            FilesystemIterator::SKIP_DOTS
        );

        $iterador = new RecursiveIteratorIterator(
            $diretorio,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterador as $item) {
            $contador++;

            if ($contador > 30000) {
                break 2;
            }

            if ($iterador->getDepth() > 7) {
                continue;
            }

            if (!$item->isFile()) {
                continue;
            }

            if (strtolower($item->getExtension()) !== 'zip') {
                continue;
            }

            $path = $item->getPathname();
            $chave = sha1(strtolower($path));

            $arquivos[$chave] = [
                'origem' => $origem,
                'path' => $path,
                'nome' => $item->getFilename(),
                'bytes' => $item->getSize(),
                'data' => $item->getMTime()
            ];

            if (count($arquivos) >= 500) {
                break 2;
            }
        }
    } catch (Throwable $e) {
        $pastas[$origem] = 'Falha na leitura';
    }
}

uasort($arquivos, fn($a, $b) => $b['data'] <=> $a['data']);

$selecionado = $_GET['backup'] ?? '';
if (!is_string($selecionado)) {
    $selecionado = '';
}

$detalhe = $arquivos[$selecionado] ?? null;

$mysqlData = 'C:/xampp/mysql/data';

$criticos = [
    'ibdata1',
    'ib_logfile0',
    'ib_logfile1',
    'mysql/roles_mapping.frm',
    'mysql/roles_mapping.MAD',
    'mysql/roles_mapping.MAI',
    'deposito_materiais/users.ibd',
    'deposito_materiais/orcamentos.ibd',
    'deposito_materiais/movimentacoes_caixa.ibd'
];

$dadosCriticos = [];

foreach ($criticos as $nome) {
    $path = $mysqlData . '/' . $nome;

    $dadosCriticos[] = [
        'nome' => $nome,
        'existe' => is_file($path),
        'bytes' => is_file($path) ? filesize($path) : 0,
        'data' => is_file($path) ? filemtime($path) : null
    ];
}

$zipEntries = [];
$zipErro = '';

if ($detalhe) {
    if (!class_exists('ZipArchive')) {
        $zipErro = 'Extensao PHP ZipArchive nao disponivel.';
    } else {
        $zip = new ZipArchive();

        if ($zip->open($detalhe['path']) === true) {
            $totalEntries = $zip->numFiles;

            for ($i = 0; $i < min($totalEntries, 150); $i++) {
                $stat = $zip->statIndex($i);

                if ($stat !== false) {
                    $zipEntries[] = [
                        'nome' => $stat['name'],
                        'bytes' => (int)$stat['size']
                    ];
                }
            }

            $zip->close();
        } else {
            $zipErro = 'Nao foi possivel abrir o arquivo ZIP.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>REC-02 | Central de Recuperacao</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    background: #101827;
    color: #e5e7eb;
    font-family: Segoe UI, Arial, sans-serif;
}
header {
    padding: 26px 5%;
    background: #17243a;
    border-bottom: 1px solid #334155;
}
h1 { margin: 0 0 8px; font-size: 27px; }
h2 { font-size: 20px; margin: 0 0 17px; }
p { color: #aebbd0; }
main { max-width: 1350px; margin: auto; padding: 24px; }
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap: 16px;
    margin-bottom: 22px;
}
.card, section {
    background: #1b2940;
    border: 1px solid #34445c;
    border-radius: 12px;
    padding: 20px;
}
.card strong { display: block; font-size: 26px; margin-top: 10px; }
.card small { color: #9fb0c6; }
section { margin-bottom: 22px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th {
    color: #9fb0c6;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #40516b;
}
td, th { padding: 12px 10px; }
td { border-bottom: 1px solid #303e54; }
tr:hover td { background: #243650; }
a { color: #76c5ff; text-decoration: none; }
a:hover { text-decoration: underline; }
.ok { color: #65dfac; }
.warn { color: #ffcb78; }
.muted { color: #aebbd0; }
.notice {
    padding: 14px 18px;
    border: 1px solid #77643e;
    background: #373020;
    color: #ffdfa0;
    border-radius: 9px;
    margin-bottom: 22px;
}
.path { overflow-wrap: anywhere; }
footer { padding: 25px; text-align: center; color: #9fb0c6; }
</style>
</head>
<body>
<header>
<h1>REC-02 | Central de Recuperacao</h1>
<p>Deposito Materiais - Diagnostico independente do Laravel e MariaDB</p>
</header>
<main>

<div class="notice">
Modo seguro: somente leitura dos backups e dos arquivos do banco.
Nenhuma restauracao ou reparacao e executada nesta versao.
</div>

<div class="cards">
<div class="card">
<small>Backups localizados</small>
<strong><?= count($arquivos) ?></strong>
</div>
<div class="card">
<small>Origens encontradas</small>
<strong><?= count(array_filter($pastas, fn($v) => $v === 'Encontrada')) ?></strong>
</div>
<div class="card">
<small>Arquivos criticos presentes</small>
<strong><?= count(array_filter($dadosCriticos, fn($v) => $v['existe'])) ?>/<?= count($criticos) ?></strong>
</div>
<div class="card">
<small>Modo de operacao</small>
<strong class="ok">Leitura</strong>
</div>
</div>

<section>
<h2>Origens de backup</h2>
<table>
<thead><tr><th>Origem</th><th>Diretorio</th><th>Situacao</th></tr></thead>
<tbody>
<?php foreach ($raizes as $origem => $path): ?>
<tr>
<td><?= h($origem) ?></td>
<td class="path"><?= h($path) ?></td>
<td><?= h($pastas[$origem]) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>

<section>
<h2>Inventario de backups</h2>
<table>
<thead>
<tr><th>Arquivo</th><th>Origem</th><th>Tamanho</th><th>Data</th><th>Inspecao</th></tr>
</thead>
<tbody>
<?php foreach ($arquivos as $id => $a): ?>
<tr>
<td><?= h($a['nome']) ?></td>
<td><?= h($a['origem']) ?></td>
<td><?= h(tamanho($a['bytes'])) ?></td>
<td><?= date('d/m/Y H:i:s', $a['data']) ?></td>
<td><a href="?backup=<?= h($id) ?>#conteudo">Ver conteudo</a></td>
</tr>
<?php endforeach; ?>
<?php if (!$arquivos): ?>
<tr><td colspan="5">Nenhum ZIP encontrado nos diretorios configurados.</td></tr>
<?php endif; ?>
</tbody>
</table>
</section>

<?php if ($detalhe): ?>
<section id="conteudo">
<h2>Conteudo do backup selecionado</h2>
<p class="path"><?= h($detalhe['path']) ?></p>
<?php if ($zipErro): ?>
<p class="warn"><?= h($zipErro) ?></p>
<?php else: ?>
<table>
<thead><tr><th>Arquivo interno</th><th>Tamanho</th></tr></thead>
<tbody>
<?php foreach ($zipEntries as $entry): ?>
<tr>
<td class="path"><?= h($entry['nome']) ?></td>
<td><?= h(tamanho($entry['bytes'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p>Exibidas ate 150 entradas. Inspecao somente leitura.</p>
<?php endif; ?>
</section>
<?php endif; ?>

<section>
<h2>Diagnostico dos arquivos MariaDB</h2>
<table>
<thead><tr><th>Arquivo</th><th>Estado</th><th>Tamanho</th><th>Ultima modificacao</th></tr></thead>
<tbody>
<?php foreach ($dadosCriticos as $item): ?>
<tr>
<td class="path"><?= h($item['nome']) ?></td>
<td class="<?= $item['existe'] ? 'ok' : 'warn' ?>">
<?= $item['existe'] ? 'Presente' : 'Ausente' ?>
</td>
<td><?= $item['existe'] ? h(tamanho($item['bytes'])) : '-' ?></td>
<td><?= $item['data'] ? date('d/m/Y H:i:s', $item['data']) : '-' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p>A presenca fisica de um arquivo nao confirma sua integridade.</p>
</section>

</main>
<footer>
REC-02 V1 - Recuperacao independente - Sem alteracoes no banco
</footer>
</body>
</html>