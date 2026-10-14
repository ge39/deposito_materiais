<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>REC-01 | Diagnóstico Geral</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f3f5f8;
            color: #253247;
        }

        .rec-container {
            max-width: 1150px;
        }

        .rec-card {
            border: 1px solid #dee3eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }

        .rec-card .card-header {
            padding: 16px 20px;
            font-weight: 600;
            background: white;
        }

        .rec-card .card-body {
            padding: 22px;
        }

        .rec-stat {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #dee3eb;
            height: 100%;
        }

        .rec-stat strong {
            display: block;
            font-size: 30px;
        }

        .rec-falha {
            border-left: 5px solid #dc3545;
        }

        code {
            overflow-wrap: anywhere;
        }

        .small-detail {
            font-size: 13px;
            color: #64748b;
        }
    </style>
</head>

<body>

<main class="container rec-container py-5">

    <header class="bg-white p-4 rounded-3 shadow-sm mb-4">
        <div class="d-flex justify-content-between
                    align-items-center flex-wrap gap-3">

            <div>
                <div class="text-muted small">
                    Administração / REC-01
                </div>

                <h3 class="mb-1">
                    Central de Recuperação de Dados
                </h3>

                <p class="text-muted mb-0">
                    Diagnóstico geral das tabelas do ERP
                </p>
            </div>

            @if(!$concluido)
                <span class="badge bg-secondary">
                    Diagnóstico inconclusivo
                </span>
            @elseif($totalAusentes > 0)
                <span class="badge bg-danger">
                    {{ $totalAusentes }} falha(s) identificada(s)
                </span>
            @else
                <span class="badge bg-success">
                    Nenhuma ausência detectada
                </span>
            @endif

        </div>
    </header>

    @if($erro)
        <div class="alert alert-warning">
            {{ $erro }}
        </div>
    @elseif($totalAusentes > 0)
        <div class="alert alert-danger p-4">
            <h4>Atenção: problemas identificados nos dados</h4>

            <p class="mb-0">
                O sistema encontrou uma ou mais tabelas
                esperadas que não estão disponíveis no
                banco de dados. Algumas funcionalidades
                podem estar interrompidas.
            </p>
        </div>
    @else
        <div class="alert alert-info">
            Não foram identificadas tabelas ausentes
            entre as referências reconhecidas pela análise.
            Isso não garante a integridade completa do banco.
        </div>
    @endif

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="rec-stat">
                <div class="text-muted">Tabelas esperadas</div>
                <strong>{{ $totalEsperadas }}</strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="rec-stat">
                <div class="text-muted">Tabelas presentes</div>
                <strong class="text-success">
                    {{ $totalPresentes }}
                </strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="rec-stat">
                <div class="text-muted">Tabelas ausentes</div>
                <strong class="text-danger">
                    {{ $totalAusentes }}
                </strong>
            </div>
        </div>

    </div>

    <section class="card rec-card mb-4">
        <div class="card-header">
            1. Tabelas com falhas identificadas
        </div>

        <div class="card-body">

            @forelse($falhas as $index => $falha)

                <div class="border rounded p-3 mb-3 rec-falha">

                    <div class="d-flex justify-content-between
                                align-items-center flex-wrap gap-2">

                        <h5 class="mb-0">
                            {{ $index + 1 }}.
                            <code>{{ $falha['tabela'] }}</code>
                        </h5>

                        <span class="badge bg-danger">
                            Tabela ausente
                        </span>
                    </div>

                    <div class="mt-3">
                        <strong>Módulo provável:</strong>
                        {{ $falha['modulo'] }}
                    </div>

                    <div class="mt-2">
                        <strong>Possíveis nomes relacionados:</strong>

                        @if(count($falha['similares']) > 0)
                            @foreach($falha['similares'] as $nome)
                                <code class="me-2">{{ $nome }}</code>
                            @endforeach
                        @else
                            <span class="text-muted">
                                Nenhum identificado
                            </span>
                        @endif
                    </div>

                    <p class="small-detail mt-2 mb-0">
                        Nomes semelhantes são apenas sugestões,
                        não comprovam que uma tabela foi renomeada.
                    </p>

                    <div class="mt-3">
                        <strong>Relacionamentos identificados:</strong>
                        {{ count($falha['dependencias']) }}
                    </div>

                    @if(count($falha['dependencias']) > 0)
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tabela relacionada</th>
                                        <th>Coluna</th>
                                        <th>Referência</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($falha['dependencias'] as $dep)
                                        <tr>
                                            <td>{{ $dep->TABLE_NAME }}</td>
                                            <td>{{ $dep->COLUMN_NAME }}</td>
                                            <td>
                                                {{ $dep->REFERENCED_TABLE_NAME }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <details class="mt-3">
                        <summary class="small text-primary">
                            Ver referências no código
                        </summary>

                        <div class="mt-2 small">
                            @foreach($falha['origens'] as $origem)
                                <div>
                                    <code>{{ $origem }}</code>
                                </div>
                            @endforeach
                        </div>
                    </details>

                    <div class="alert alert-warning mt-3 mb-0">
                        Recuperação não autorizada.
                        Nenhuma alteração foi realizada.
                    </div>

                </div>

            @empty
                <p class="text-muted mb-0">
                    Nenhuma ausência foi confirmada pelo
                    inventário de código analisado.
                </p>
            @endforelse

        </div>
    </section>

    <section class="card rec-card mb-4">
        <div class="card-header">
            2. Backups automáticos localizados
        </div>

        <div class="card-body">

            @if(count($backups) > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Data</th>
                                <th>Tamanho</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($backups as $backup)
                                <tr>
                                    <td>
                                        <code>{{ $backup['nome'] }}</code>
                                    </td>

                                    <td>
                                        {{ date('d/m/Y H:i', $backup['data']) }}
                                    </td>

                                    <td>
                                        {{ number_format(
                                            $backup['tamanho'] / 1048576,
                                            2, ',', '.'
                                        ) }} MB
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mb-0">
                    Backups localizados no armazenamento.
                    A compatibilidade de cada backup com
                    cada tabela ainda será avaliada na simulação.
                </div>
            @else
                <div class="alert alert-warning mb-0">
                    Nenhum backup foi localizado nesta consulta.
                </div>
            @endif

        </div>
    </section>

    <section class="card rec-card mb-4">
        <div class="card-header">
            3. Como deseja prosseguir?
        </div>

        <div class="card-body">

            <p>
                O diagnóstico identifica tabelas ausentes.
                A próxima etapa avaliará individualmente
                os impactos de cada recuperação.
            </p>

            <div class="alert alert-warning">
                Não execute restaurações ou renomeações
                sem verificar os relacionamentos e as
                consequências para os demais módulos.
            </div>

            <div class="d-grid gap-2">

                <button class="btn btn-primary"
                        type="button"
                        disabled>
                    Simular recuperação — próxima etapa
                </button>

                <button class="btn btn-outline-secondary"
                        type="button"
                        onclick="copiarDiagnostico()">
                    Copiar diagnóstico para o desenvolvedor
                </button>
            </div>

            <div id="rec01-msg"
                 class="small mt-3"
                 role="status"
                 aria-live="polite"></div>

        </div>
    </section>

    <section class="card rec-card mb-4">
        <div class="card-body">

            <h5>Não se sente seguro para continuar?</h5>

            <p class="mb-0">
                Interrompa o procedimento e entre em contato
                com o desenvolvedor do ERP ou responsável
                técnico. Encaminhe o diagnóstico para
                facilitar a análise e evitar alterações indevidas.
            </p>

        </div>
    </section>

    <footer class="text-center text-muted small py-3">
        REC-01 V4 — Inventário automático e diagnóstico
        somente leitura. Nenhuma restauração habilitada.
    </footer>

</main>

@php
    $rec01Relatorio = [
        'banco' => $banco,
        'concluido' => $concluido,
        'esperadas' => $totalEsperadas,
        'presentes' => $totalPresentes,
        'ausentes' => $totalAusentes,
        'falhas' => array_map(
            fn ($f) => [
                'tabela' => $f['tabela'],
                'modulo' => $f['modulo'],
                'similares' => $f['similares'],
                'dependencias' => count($f['dependencias']),
            ],
            $falhas
        ),
        'backups' => array_column($backups, 'nome'),
    ];
@endphp

<script>
const dadosREC01 = {{ \Illuminate\Support\Js::from($rec01Relatorio) }};

async function copiarDiagnostico() {
    const texto = [
        'REC-01 V4 - Diagnóstico Geral',
        'Banco: ' + dadosREC01.banco,
        'Diagnóstico concluído: ' +
            (dadosREC01.concluido ? 'Sim' : 'Não'),
        'Tabelas esperadas: ' + dadosREC01.esperadas,
        'Tabelas presentes: ' + dadosREC01.presentes,
        'Tabelas ausentes: ' + dadosREC01.ausentes,
        '',
        ...dadosREC01.falhas.map(f =>
            'Tabela: ' + f.tabela +
            ' | Módulo provável: ' + f.modulo +
            ' | Similares: ' + f.similares.join(', ') +
            ' | Relacionamentos: ' + f.dependencias
        ),
        '',
        'Backups localizados:',
        ...dadosREC01.backups,
        '',
        'Recuperação executada: NÃO'
    ].join('\n');

    const msg = document.getElementById('rec01-msg');

    try {
        await navigator.clipboard.writeText(texto);
        msg.className = 'small mt-3 text-success';
        msg.textContent = 'Diagnóstico copiado.';
    } catch (error) {
        msg.className = 'small mt-3 text-danger';
        msg.textContent = 'Não foi possível copiar.';
    }
}
</script>

</body>
</html>