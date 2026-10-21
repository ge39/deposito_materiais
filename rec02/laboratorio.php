<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

function h($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

$base = dirname(__DIR__);
$manifesto = $base . '/storage/app/rec02_laboratorio/manifesto.json';

$dados = null;
$erro = '';

if (is_file($manifesto)) {
    try {
        $conteudo = file_get_contents($manifesto);

        if ($conteudo === false) {
            throw new RuntimeException('Falha de leitura.');
        }

        $dados = json_decode(
            preg_replace('/^\xEF\xBB\xBF/', '', $conteudo),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    } catch (Throwable $e) {
        $erro = 'Manifesto invalido ou inacessivel.';
    }
} else {
    $erro = 'Manifesto nao encontrado.';
}

$lab = $dados['laboratorio'] ?? [];
$backup = $dados['backup'] ?? [];
$sql = $dados['sql'] ?? [];
$ferramentas = $dados['ferramentas'] ?? [];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>REC-02 V3 | Laboratorio</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    background: #101827;
    color: #e5e7eb;
    font-family: Segoe UI, Arial, sans-serif;
}
header {
    padding: 25px 5%;
    background: #19283f;
    border-bottom: 1px solid #34445c;
}
main {
    max-width: 1320px;
    margin: auto;
    padding: 25px;
}
h1 { margin: 0 0 8px; }
h2 { font-size: 20px; }
p { color: #bac8db; }
a { color: #80caff; }
section {
    margin-bottom: 20px;
    padding: 22px;
    border-radius: 12px;
    background: #1b2940;
    border: 1px solid #34445c;
}
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.card {
    background: #1b2940;
    padding: 18px;
    border: 1px solid #34445c;
    border-radius: 12px;
}
.card strong {
    display: block;
    margin-top: 12px;
    font-size: 21px;
}
small { color: #a9bdd5; }
.ok { color: #69dda6; }
.warn { color: #ffcb78; }
table {
    width: 100%;
    border-collapse: collapse;
}
th,td {
    padding: 12px;
    border-bottom: 1px solid #34445c;
    text-align: left;
}
th { color: #a9c9f3; }
.path, code { overflow-wrap: anywhere; }
.notice {
    color: #ffda94;
    border: 1px solid #71623b;
    background: #343020;
    padding: 15px;
    border-radius: 9px;
    margin-bottom: 20px;
}
</style>
</head>
<body>

<header>
<h1>REC-02 V3 — Laboratorio de Restauracao</h1>
<p>Ambiente independente para preparacao de recuperacao do ERP</p>
</header>

<main>

<p><a href="index.php">Central REC-02</a> |
<a href="auditoria.php">Auditoria V2</a></p>

<div class="notice">
Etapa de preparacao. O banco de laboratorio ainda nao foi
inicializado e nenhuma importacao SQL foi executada.
</div>

<?php if ($erro !== ''): ?>
<section><p class="warn"><?= h($erro) ?></p></section>
<?php elseif ($dados !== null): ?>

<div class="cards">
<div class="card">
<small>Backup SQL</small>
<strong class="ok">Preparado</strong>
</div>
<div class="card">
<small>Porta planejada</small>
<strong><?= h($lab['porta_planejada'] ?? '-') ?></strong>
</div>
<div class="card">
<small>Banco isolado</small>
<strong class="warn">Nao iniciado</strong>
</div>
<div class="card">
<small>Importacao SQL</small>
<strong>Nao executada</strong>
</div>
</div>

<section>
<h2>Backup selecionado</h2>
<p><strong>Arquivo:</strong> <?= h($backup['nome'] ?? '-') ?></p>
<p class="path"><strong>Origem:</strong>
<?= h($backup['origem'] ?? '-') ?></p>
<p><strong>SHA-256 ZIP:</strong>
<code><?= h($backup['sha256'] ?? '-') ?></code></p>
</section>

<section>
<h2>Dump SQL preparado</h2>
<p class="path"><strong>Arquivo:</strong>
<?= h($sql['arquivo'] ?? '-') ?></p>
<p><strong>Tamanho:</strong>
<?= number_format(
    (int)($sql['tamanho'] ?? 0) / 1048576,
    2, ',', '.'
) ?> MB</p>
<p><strong>SHA-256 SQL:</strong>
<code><?= h($sql['sha256'] ?? '-') ?></code></p>
</section>

<section>
<h2>Ferramentas MariaDB</h2>
<table>
<thead>
<tr><th>Ferramenta</th><th>Disponibilidade</th></tr>
</thead>
<tbody>
<?php foreach ($ferramentas as $f): ?>
<tr>
<td><?= h($f['Ferramenta'] ?? '-') ?></td>
<td class="<?= !empty($f['Encontrada']) ? 'ok' : 'warn' ?>">
<?= !empty($f['Encontrada']) ? 'Encontrada' : 'Ausente' ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>

<section>
<h2>Estado da recuperacao</h2>
<p><?= h($dados['status'] ?? '-') ?></p>
<p>Diretorio: <code><?= h($lab['diretorio'] ?? '-') ?></code></p>
<p>Porta de laboratorio:
<?= !empty($lab['porta_disponivel'])
    ? 'Disponivel na verificacao'
    : 'Ocupada na verificacao' ?></p>
</section>

<?php endif; ?>

</main>
</body>
</html>