<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Acesso permitido somente pelo computador local.');
}

function h($value): string {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function tamanho(int $n): string {
    return number_format($n / 1048576, 2, ',', '.') . ' MB';
}

function arquivoInfo(string $path): array {
    clearstatcache(true, $path);

    if (!is_file($path)) {
        return [
            'existe' => false,
            'tamanho' => 0,
            'data' => '-'
        ];
    }

    return [
        'existe' => true,
        'tamanho' => filesize($path),
        'data' => date('d/m/Y H:i:s', filemtime($path))
    ];
}

function logFinal(string $path): string {
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }

    $fp = fopen($path, 'rb');

    if ($fp === false) {
        return '';
    }

    try {
        $size = filesize($path);

        if ($size === false) {
            return '';
        }

        $limite = 1048576;
        $inicio = max(0, $size - $limite);

        fseek($fp, $inicio);

        $texto = stream_get_contents($fp);

        return $texto === false ? '' : $texto;
    } finally {
        fclose($fp);
    }
}

$data = 'C:/xampp/mysql/data';
$log = $data . '/mysql_error.log';

$texto = logFinal($log);
$linhas = preg_split('/\r\n|\r|\n/', $texto) ?: [];

$erros = [];

$padroes = [
    'roles_mapping' => [
        'titulo' => 'Falha na tabela interna roles_mapping',
        'regex' => '/Incorrect file format.*roles_mapping/i',
        'gravidade' => 'CRITICA'
    ],
    'privilegios' => [
        'titulo' => 'Falha nas tabelas internas de privilegios',
        'regex' => '/Can.t open and lock privilege tables/i',
        'gravidade' => 'CRITICA'
    ],
    'innodb' => [
        'titulo' => 'Erro do mecanismo InnoDB',
        'regex' => '/\[ERROR\].*InnoDB/i',
        'gravidade' => 'CRITICA'
    ],
    'aria' => [
        'titulo' => 'Erro do mecanismo Aria',
        'regex' => '/\[ERROR\].*Aria/i',
        'gravidade' => 'CRITICA'
    ],
    'tablespace' => [
        'titulo' => 'Problema com tablespace InnoDB',
        'regex' => '/\[ERROR\].*(tablespace|ibdata|ib_logfile)/i',
        'gravidade' => 'CRITICA'
    ],
    'porta' => [
        'titulo' => 'Possivel conflito de porta',
        'regex' => '/(?:Bind on TCP\/IP port|Do you already have another server running|Address already in use)/i',
        'gravidade' => 'ATENCAO'
    ]
];

foreach ($padroes as $id => $p) {
    $ocorrencias = [];

    foreach ($linhas as $linha) {
        if (preg_match($p['regex'], $linha)) {
            $ocorrencias[] = trim($linha);
        }
    }

    if ($ocorrencias) {
        $erros[$id] = [
            'titulo' => $p['titulo'],
            'gravidade' => $p['gravidade'],
            'quantidade' => count($ocorrencias),
            'ultima' => end($ocorrencias)
        ];
    }
}

$nomes = [
    'ibdata1',
    'ib_logfile0',
    'ib_logfile1',
    'mysql/roles_mapping.frm',
    'mysql/roles_mapping.MAD',
    'mysql/roles_mapping.MAI'
];

$arquivos = [];

foreach ($nomes as $nome) {
    $arquivos[$nome] = arquivoInfo($data . '/' . $nome);
}

$referencias = [];

foreach (['frm', 'MAD', 'MAI'] as $ext) {
    $nome = 'roles_mapping.' . $ext;

    $referencias[$nome] = arquivoInfo(
        'C:/xampp/mysql/backup/mysql/' . $nome
    );
}

$indicadores = [
    'novos_logs' => preg_match('/New log files created/i', $texto) === 1,
    'doublewrite' => preg_match('/Doublewrite buffer not found/i', $texto) === 1,
    'system_tables' => preg_match('/Creating foreign key constraint system tables/i', $texto) === 1
];

$logExiste = is_file($log);
$logData = $logExiste
    ? date('d/m/Y H:i:s', filemtime($log))
    : '-';

$ultimaLinha = '';

foreach (array_reverse($linhas) as $linha) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}.*\[(?:ERROR|Warning)\]/i', $linha)) {
        $ultimaLinha = trim($linha);
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>REC-02 | Diagnostico MariaDB</title>
<style>
* { box-sizing:border-box; }
body {
    margin:0;
    background:#101827;
    color:#e5e7eb;
    font-family:Segoe UI,Arial,sans-serif;
}
header {
    padding:25px 5%;
    background:#19283f;
    border-bottom:1px solid #34445c;
}
main { max-width:1350px; margin:auto; padding:25px; }
h1 { margin:0 0 8px; }
h2 { font-size:20px; margin-top:0; }
p { color:#b7c6d9; line-height:1.6; }
a { color:#83caff; }
section {
    background:#1b2940;
    border:1px solid #34445c;
    border-radius:12px;
    padding:22px;
    margin-bottom:20px;
    overflow-x:auto;
}
.cards {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
    gap:15px;
    margin-bottom:20px;
}
.card {
    padding:20px;
    background:#1b2940;
    border:1px solid #34445c;
    border-radius:12px;
}
.card strong { display:block; font-size:24px; margin-top:10px; }
small { color:#a8b9cd; }
table { width:100%; border-collapse:collapse; }
th,td {
    padding:12px;
    border-bottom:1px solid #34445c;
    text-align:left;
    vertical-align:top;
}
th { color:#a6c9f4; }
.ok { color:#69dda6; }
.warn { color:#ffcd7b; }
.danger { color:#ff9090; }
.code {
    font-family:Consolas,monospace;
    font-size:13px;
    overflow-wrap:anywhere;
}
.notice {
    background:#343020;
    border:1px solid #75653c;
    border-radius:9px;
    padding:15px;
    margin-bottom:20px;
    color:#ffdda0;
}
.btn {
    display:inline-block;
    background:#275c92;
    padding:10px 15px;
    color:white;
    text-decoration:none;
    border-radius:7px;
}
</style>
</head>
<body>
<header>
<h1>REC-02 V3.2 — Diagnostico MariaDB</h1>
<p>Deposito Materiais | Analise independente do Laravel e do banco</p>
</header>
<main>

<p>
<a href="index.php">Central REC-02</a> |
<a href="auditoria.php">Auditoria de Backups</a> |
<a href="laboratorio.php">Laboratorio V3</a>
</p>

<div class="notice">
Diagnostico somente leitura. Nenhum reparo, exclusao,
restauracao ou comando MariaDB e executado.
</div>

<div class="cards">
<div class="card">
<small>Tipos de ocorrencias no trecho analisado</small>
<strong><?= count($erros) ?></strong>
</div>
<div class="card">
<small>Arquivos criticos presentes</small>
<strong>
<?= count(array_filter($arquivos, fn($x) => $x['existe'])) ?>
/<?= count($arquivos) ?>
</strong>
</div>
<div class="card">
<small>Log MariaDB</small>
<strong class="<?= $logExiste ? 'ok' : 'danger' ?>">
<?= $logExiste ? 'Disponivel' : 'Ausente' ?>
</strong>
</div>
<div class="card">
<small>Modo de operacao</small>
<strong class="ok">Leitura</strong>
</div>
</div>

<section>
<h2>Estado do diagnostico</h2>
<p>Log analisado: <span class="code"><?= h($log) ?></span></p>
<p>Ultima modificacao: <?= h($logData) ?></p>
<p>Janela analisada: ate os ultimos 1 MiB do log.</p>
<p>
Este painel analisa registros historicos. Uma ocorrencia
antiga nao comprova que a falha continua ativa.
</p>
<?php if ($ultimaLinha !== ''): ?>
<p><strong>Ultimo erro ou aviso registrado:</strong></p>
<p class="code"><?= h($ultimaLinha) ?></p>
<?php endif; ?>
</section>

<section>
<h2>Falhas identificadas no log</h2>
<table>
<thead>
<tr>
<th>Falha</th>
<th>Gravidade</th>
<th>Ocorrencias</th>
<th>Ultimo registro</th>
</tr>
</thead>
<tbody>

<?php foreach ($erros as $erro): ?>
<tr>
<td><?= h($erro['titulo']) ?></td>
<td class="danger"><?= h($erro['gravidade']) ?></td>
<td><?= h($erro['quantidade']) ?></td>
<td class="code"><?= h($erro['ultima']) ?></td>
</tr>
<?php endforeach; ?>

<?php if (!$erros): ?>
<tr>
<td colspan="4">Nenhuma das falhas catalogadas foi encontrada
no trecho analisado.</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</section>

<section>
<h2>Arquivos fisicos do MariaDB</h2>

<table>
<thead>
<tr>
<th>Arquivo</th>
<th>Situacao</th>
<th>Tamanho</th>
<th>Modificacao</th>
</tr>
</thead>
<tbody>

<?php foreach ($arquivos as $nome => $a): ?>
<tr>
<td class="code"><?= h($nome) ?></td>
<td class="<?= $a['existe'] ? 'ok' : 'danger' ?>">
<?= $a['existe'] ? 'Presente' : 'Ausente' ?>
</td>
<td><?= $a['existe'] ? h(tamanho($a['tamanho'])) : '-' ?></td>
<td><?= h($a['data']) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</section>

<section>
<h2>Comparacao com os arquivos de referencia XAMPP</h2>

<table>
<thead>
<tr>
<th>Arquivo</th>
<th>Atual</th>
<th>Referencia</th>
</tr>
</thead>
<tbody>

<?php foreach ($referencias as $nome => $referencia):
    $atual = $arquivos['mysql/' . $nome];
?>
<tr>
<td><?= h($nome) ?></td>
<td><?= h(tamanho($atual['tamanho'])) ?></td>
<td><?= h(tamanho($referencia['tamanho'])) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<p>
Diferenca de tamanho nao comprova corrupcao.
Os arquivos de referencia nao devem ser copiados
automaticamente sobre a instalacao atual.
</p>
</section>

<section>
<h2>Indicadores de reinicializacao do InnoDB</h2>

<table>
<thead>
<tr><th>Indicador no log</th><th>Resultado</th></tr>
</thead>
<tbody>
<tr>
<td>Criacao de novos redo logs</td>
<td><?= $indicadores['novos_logs'] ? 'Identificado' : 'Nao identificado' ?></td>
</tr>
<tr>
<td>Criacao de doublewrite buffer</td>
<td><?= $indicadores['doublewrite'] ? 'Identificado' : 'Nao identificado' ?></td>
</tr>
<tr>
<td>Criacao de tabelas internas do InnoDB</td>
<td><?= $indicadores['system_tables'] ? 'Identificado' : 'Nao identificado' ?></td>
</tr>
</tbody>
</table>
<p>
Estes indicadores exigem analise do historico dos
tablespaces antes de qualquer reparacao.
</p>
</section>

<section>
<h2>Procedimento de recuperacao recomendado</h2>

<?php if (isset($erros['roles_mapping'])): ?>
<p class="danger">
Foi encontrado erro de formato na tabela interna
mysql.roles_mapping.
</p>
<p>
A proxima etapa devera validar uma copia de seguranca
do estado atual e preparar um procedimento seletivo
para a tabela interna afetada.
</p>
<?php else: ?>
<p>
Nao foi identificada a assinatura roles_mapping
na janela de log analisada.
</p>
<?php endif; ?>

<p>
A restauracao do backup SQL do ERP deve ocorrer em
uma instancia isolada e ser validada antes de qualquer
substituicao da base operacional.
</p>

<p class="warn">
Reparo automatico desabilitado nesta versao.
</p>
</section>

<section>
<h2>Acoes disponiveis</h2>
<p>
<a class="btn" href="diagnostico.php">
Atualizar diagnostico
</a>
</p>
<p>
Recuperacao seletiva: aguardando implementacao,
validacao e autorizacao.
</p>
</section>

</main>
</body>
</html>