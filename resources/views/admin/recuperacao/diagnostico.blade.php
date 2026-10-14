<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>REC-01 | Central de Recuperação</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f3f6fa;
            color: #253247;
        }

        .rec-container {
            max-width: 1050px;
            margin: 40px auto;
        }

        .rec-header {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border-left: 5px solid #dc3545;
        }

        .rec-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,.04);
        }

        .rec-card .card-header {
            background: #fff;
            padding: 16px 22px;
            font-weight: 600;
        }

        .rec-card .card-body {
            padding: 22px;
        }

        .rec-label {
            color: #64748b;
            font-size: 14px;
        }

        .rec-value {
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .rec-alert {
            border-left: 5px solid #dc3545;
        }

        .rec-safe {
            border-left: 5px solid #198754;
        }

        .rec-support {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 20px;
        }

        .rec-step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 15px;
        }

        .rec-step-number {
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            min-width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 13px;
        }

        .btn {
            border-radius: 8px;
            padding: 11px 18px;
        }

        @media (max-width: 768px) {
            .rec-container {
                margin: 15px auto;
            }
        }
    </style>
</head>

<body>

<main class="container rec-container">

    <!-- CABECALHO -->

    <section class="rec-header mb-4">

        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">

            <div>
                <div class="text-muted small mb-1">
                    Administração / Integridade dos Dados / REC-01
                </div>

                <h3 class="mb-1">
                    Central de Recuperação de Dados
                </h3>

                <p class="text-muted mb-0">
                    Diagnóstico de falhas e recuperação segura do ERP
                </p>
            </div>

            <span class="badge bg-danger fs-6">
                Falha detectada
            </span>

        </div>

    </section>

    <!-- ALERTA PRINCIPAL -->

    <div class="alert alert-danger rec-alert p-4 mb-4">

        <h4 class="alert-heading">
            Atenção: identificamos um problema nos dados
        </h4>

        <p>
            O sistema detectou uma ruptura no acesso a informações
            essenciais do módulo de Orçamentos.
        </p>

        <p>
            Algumas operações podem não funcionar corretamente
            até que o problema seja resolvido.
        </p>

        <hr>

        <p class="mb-0">
            <strong>Importante:</strong>
            isso não significa necessariamente que seus dados
            foram perdidos. O sistema identificou uma
            inconsistência que precisa ser analisada.
        </p>

    </div>

    <!-- DIAGNOSTICO -->

    <section class="card rec-card mb-4">

        <div class="card-header">
            1. Resultado do diagnóstico
        </div>

        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="rec-label">Banco de dados</div>
                    <div class="rec-value">deposito_materiais</div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Módulo afetado</div>
                    <div class="rec-value">Orçamentos</div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Tabela esperada</div>
                    <div class="rec-value">
                        <code>orcamentos</code>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Tipo de problema</div>
                    <div class="rec-value">
                        Tabela não encontrada
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Código técnico</div>
                    <div class="rec-value">
                        MySQL 1146 / SQLSTATE 42S02
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Situação atual</div>
                    <span class="badge bg-danger">
                        Necessita intervenção
                    </span>
                </div>

            </div>

            <hr>

            <h6>O que isso significa?</h6>

            <p class="text-muted mb-0">
                O sistema tentou acessar uma tabela necessária
                para executar suas operações, mas ela não foi
                encontrada com o nome esperado.
                Antes de qualquer recuperação, é necessário
                analisar a estrutura e os relacionamentos.
            </p>

        </div>

    </section>

    <!-- BACKUP -->

    <section class="card rec-card mb-4">

        <div class="card-header">
            2. Backup de referência identificado
        </div>

        <div class="card-body">

            <div class="alert alert-success rec-safe">

                O sistema possui um backup anterior à
                falha controlada, localizado no armazenamento
                de backups do ERP.

            </div>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="rec-label">Data do backup</div>
                    <div class="rec-value">
                        13/10/2026 — 20:00
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Origem</div>
                    <div class="rec-value">
                        Backup Automático
                    </div>
                </div>

                <div class="col-12">
                    <div class="rec-label">Arquivo</div>
                    <div class="rec-value">
                        <code>backup_2026-10-13_20-00-15.zip</code>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Recuperação</div>
                    <span class="badge bg-warning text-dark">
                        Aguardando simulação
                    </span>
                </div>

                <div class="col-md-6">
                    <div class="rec-label">Banco de dados</div>
                    <span class="badge bg-secondary">
                        Nenhuma alteração realizada
                    </span>
                </div>

            </div>

            <hr>

            <p class="text-muted mb-0">
                O backup será utilizado como referência para
                avaliar uma possível recuperação seletiva.
                A compatibilidade da estrutura e dos dados
                ainda precisa ser verificada.
            </p>

        </div>

    </section>

    <!-- PROCEDIMENTO -->

    <section class="card rec-card mb-4">

        <div class="card-header">
            3. Como funciona a recuperação?
        </div>

        <div class="card-body">

            <div class="rec-step">
                <div class="rec-step-number">1</div>
                <div>
                    <strong>Identificação da falha</strong>
                    <div class="text-muted">
                        Detectamos a tabela e o módulo afetado.
                    </div>
                </div>
            </div>

            <div class="rec-step">
                <div class="rec-step-number">2</div>
                <div>
                    <strong>Identificação do backup</strong>
                    <div class="text-muted">
                        Localizamos uma cópia anterior ao problema.
                    </div>
                </div>
            </div>

            <div class="rec-step">
                <div class="rec-step-number">3</div>
                <div>
                    <strong>Simulação da recuperação</strong>
                    <div class="text-muted">
                        Será avaliado o impacto da restauração
                        sobre os registros e relacionamentos.
                    </div>
                </div>
            </div>

            <div class="rec-step">
                <div class="rec-step-number">4</div>
                <div>
                    <strong>Autorização do administrador</strong>
                    <div class="text-muted">
                        Nenhum dado será restaurado sem
                        confirmação expressa.
                    </div>
                </div>
            </div>

            <div class="rec-step mb-0">
                <div class="rec-step-number">5</div>
                <div>
                    <strong>Recuperação e validação</strong>
                    <div class="text-muted">
                        Após a autorização, serão executadas
                        as operações aprovadas e suas verificações.
                    </div>
                </div>
            </div>

        </div>

    </section>

    <!-- ORIENTACAO AO USUARIO -->

    <section class="card rec-card mb-4">

        <div class="card-header">
            4. Como deseja prosseguir?
        </div>

        <div class="card-body">

            <p>
                Você pode solicitar a análise de recuperação
                ou encaminhar o problema para o desenvolvedor.
            </p>

            <div class="alert alert-warning">
                <strong>Atenção:</strong>
                não renomeie tabelas, não execute comandos SQL
                e não importe backups manualmente sem a
                orientação de um profissional responsável.
            </div>

            <div class="d-grid gap-3">

                <button type="button"
                        class="btn btn-primary"
                        disabled
                        title="Disponível após integração do simulador">
                    Iniciar simulação de recuperação
                    (em desenvolvimento)
                </button>

                <button type="button"
                        class="btn btn-outline-secondary"
                        onclick="copiarDiagnostico()">
                    Copiar diagnóstico para o desenvolvedor
                </button>

            </div>

            <div id="mensagem-copia"
                 class="mt-3 small"
                 role="status"
                 aria-live="polite"></div>

        </div>

    </section>

    <!-- SUPORTE -->

    <section class="rec-support mb-4">

        <h5>Não se sente seguro para continuar?</h5>

        <p>
            Não há problema em interromper o procedimento.
            Uma recuperação inadequada pode comprometer
            informações importantes da empresa.
        </p>

        <p class="mb-0">
            Recomendamos entrar em contato com o
            <strong>desenvolvedor do sistema</strong>
            ou com o responsável técnico pelo banco de dados.
            Encaminhe o diagnóstico apresentado nesta tela
            para facilitar o atendimento.
        </p>

    </section>

    <!-- RODAPE -->

    <footer class="text-center text-muted small py-3">

        <strong>REC-01</strong>
        — Diagnóstico e Recuperação Seletiva de Tabelas

        <div class="mt-1">
            Interface demonstrativa V1 —
            Nenhuma operação de restauração habilitada.
        </div>

    </footer>

</main>

<script>
async function copiarDiagnostico() {

    const texto = [
        "REC-01 - Diagnóstico de Banco de Dados",
        "Banco: deposito_materiais",
        "Módulo: Orçamentos",
        "Tabela esperada: orcamentos",
        "Falha: Tabela não encontrada",
        "Erro: MySQL 1146 / SQLSTATE 42S02",
        "Backup: backup_2026-10-13_20-00-15.zip",
        "Data: 13/10/2026 20:00",
        "Situação: Aguardando simulação",
        "Recuperação executada: Não"
    ].join("\n");

    const mensagem = document.getElementById("mensagem-copia");

    try {
        await navigator.clipboard.writeText(texto);

        mensagem.className = "mt-3 small text-success";
        mensagem.textContent =
            "Diagnóstico copiado. Encaminhe ao desenvolvedor.";
    } catch (error) {
        mensagem.className = "mt-3 small text-danger";
        mensagem.textContent =
            "Não foi possível copiar automaticamente. " +
            "Verifique as permissões do navegador.";
    }
}
</script>

</body>
</html>