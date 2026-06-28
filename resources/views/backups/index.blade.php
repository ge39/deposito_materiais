@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @php
        $ultimoBackup = $arquivos->first();
        $historicoBackups = $arquivos->skip(1);

        $ultimoLog = $logs->first();
        $historicoLogs = $logs->skip(1);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-database-check me-2"></i>
                Backup do Sistema
            </h4>
            <small class="text-muted">
                Segurança, restauração e retenção dos dados do ERP.
            </small>
        </div>

        <form action="{{ route('backups.gerar') }}" method="POST" id="formGerarBackup">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-cloud-arrow-down me-1"></i>
                Gerar Novo Backup
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="alert alert-{{ $statusBackup['classe'] }} d-flex align-items-center shadow-sm py-3">
        <i class="bi {{ $statusBackup['icone'] }} fs-3 me-3"></i>
        <div>
            <div class="fw-bold">{{ $statusBackup['texto'] }}</div>
            <small>{{ $statusBackup['descricao'] }}</small>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-clock-history me-1"></i>Último Backup</small>
                    <div class="fw-bold mt-1">{{ $resumo['ultimo_backup'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-success border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-archive me-1"></i>Total</small>
                    <div class="fw-bold mt-1">{{ $resumo['total_backups'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-hdd me-1"></i>Espaço Utilizado</small>
                    <div class="fw-bold mt-1">{{ $resumo['espaco_total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-info border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>Retenção</small>
                    <div class="fw-bold mt-1">{{ $resumo['retencao'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-secondary border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-cloud me-1"></i>Driver</small>
                    <div class="fw-bold mt-1">{{ $resumo['driver'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-info-circle me-1"></i>
                    Informações do Ambiente
                </div>
                <div class="card-body small">
                    <div><strong>Driver:</strong> {{ $resumo['driver'] }}</div>
                    <div><strong>Destino:</strong> {{ $resumo['destino'] }}</div>
                    <div><strong>Compressão:</strong> {{ $resumo['compressao'] }}</div>
                    <div><strong>Retenção:</strong> {{ $resumo['retencao'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-list-check me-1"></i>
                    Recomendações
                </div>
                <div class="card-body small">
                    <div>• Gere backup antes de atualizações, migrações ou alterações críticas.</div>
                    <div>• Baixe uma cópia externa periodicamente.</div>
                    <div>• Teste restauração apenas em ambiente controlado.</div>
                    <div>• Mantenha a retenção configurada conforme o espaço disponível.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- BACKUPS DISPONÍVEIS --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-file-earmark-zip me-1"></i>
            Backups disponíveis
        </div>

        <div class="card-body p-0">
            @if($arquivos->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Nenhum backup encontrado.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Arquivo</th>
                                <th class="text-center">Data</th>
                                <th class="text-center">Hora</th>
                                <th class="text-end">Tamanho</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if($ultimoBackup)
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-zip text-warning me-2"></i>
                                        <strong>{{ $ultimoBackup['nome'] }}</strong>
                                    </td>
                                    <td class="text-center">{{ $ultimoBackup['data'] }}</td>
                                    <td class="text-center">{{ $ultimoBackup['hora'] }}</td>
                                    <td class="text-end">{{ $ultimoBackup['tamanho'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">Completo</span>
                                    </td>
                                    <td class="text-center">
                                        @if($ultimoBackup['valido'])
                                            <span class="badge bg-success">Válido</span>
                                        @else
                                            <span class="badge bg-danger">Inválido</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('backups.download', $ultimoBackup['nome']) }}"
                                           class="btn btn-success btn-sm"
                                           title="Baixar">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        <form action="{{ route('backups.restaurar') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="arquivo" value="{{ $ultimoBackup['nome'] }}">
                                            <button type="submit"
                                                    class="btn btn-warning btn-sm"
                                                    title="Restaurar"
                                                    onclick="return confirm('ATENÇÃO: restaurar este backup pode sobrescrever os dados atuais. Deseja continuar?')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('backups.destroy', $ultimoBackup['nome']) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    title="Excluir"
                                                    onclick="return confirm('Deseja excluir este backup?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($historicoBackups->isNotEmpty())
                    <div class="accordion border-top" id="accordionHistoricoBackups">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-light py-2 fw-bold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapseHistoricoBackups"
                                        aria-expanded="false"
                                        aria-controls="collapseHistoricoBackups">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Histórico de backups anteriores ({{ $historicoBackups->count() }})
                                </button>
                            </h2>

                            <div id="collapseHistoricoBackups"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#accordionHistoricoBackups">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Arquivo</th>
                                                    <th class="text-center">Data</th>
                                                    <th class="text-center">Hora</th>
                                                    <th class="text-end">Tamanho</th>
                                                    <th class="text-center">Tipo</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Ações</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($historicoBackups as $backup)
                                                    <tr>
                                                        <td>
                                                            <i class="bi bi-file-earmark-zip text-warning me-2"></i>
                                                            <strong>{{ $backup['nome'] }}</strong>
                                                        </td>
                                                        <td class="text-center">{{ $backup['data'] }}</td>
                                                        <td class="text-center">{{ $backup['hora'] }}</td>
                                                        <td class="text-end">{{ $backup['tamanho'] }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary">Completo</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($backup['valido'])
                                                                <span class="badge bg-success">Válido</span>
                                                            @else
                                                                <span class="badge bg-danger">Inválido</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('backups.download', $backup['nome']) }}"
                                                               class="btn btn-success btn-sm"
                                                               title="Baixar">
                                                                <i class="bi bi-download"></i>
                                                            </a>

                                                            <form action="{{ route('backups.restaurar') }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="arquivo" value="{{ $backup['nome'] }}">
                                                                <button type="submit"
                                                                        class="btn btn-warning btn-sm"
                                                                        title="Restaurar"
                                                                        onclick="return confirm('ATENÇÃO: restaurar este backup pode sobrescrever os dados atuais. Deseja continuar?')">
                                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                                </button>
                                                            </form>

                                                            <form action="{{ route('backups.destroy', $backup['nome']) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="btn btn-danger btn-sm"
                                                                        title="Excluir"
                                                                        onclick="return confirm('Deseja excluir este backup?')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ÚLTIMAS OPERAÇÕES DE BACKUP --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-clock-history me-1"></i>
            Últimas operações de backup
        </div>

        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="p-4 text-center text-muted">
                    Nenhum log registrado até o momento.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ação</th>
                                <th>Arquivo</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Tamanho</th>
                                <th class="text-center">Duração</th>
                                <th>Mensagem</th>
                                <th class="text-center">Data</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if($ultimoLog)
                                <tr>
                                    <td>
                                        <strong>{{ $ultimoLog->acao }}</strong>
                                    </td>

                                    <td>{{ $ultimoLog->arquivo ?? '-' }}</td>

                                    <td class="text-center">
                                        @if($ultimoLog->status === 'sucesso')
                                            <span class="badge bg-success">Sucesso</span>
                                        @elseif($ultimoLog->status === 'erro')
                                            <span class="badge bg-danger">Erro</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        {{ number_format(($ultimoLog->tamanho_bytes ?? 0) / 1024 / 1024, 2, ',', '.') }} MB
                                    </td>

                                    <td class="text-center">
                                        {{ $ultimoLog->duracao_ms ? number_format($ultimoLog->duracao_ms / 1000, 2, ',', '.') . 's' : '-' }}
                                    </td>

                                    <td>
                                        <small>{{ $ultimoLog->mensagem ?? '-' }}</small>
                                    </td>

                                    <td class="text-center">
                                        {{ optional($ultimoLog->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($historicoLogs->isNotEmpty())
                    <div class="accordion border-top" id="accordionHistoricoLogs">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-light py-2 fw-bold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapseHistoricoLogs"
                                        aria-expanded="false"
                                        aria-controls="collapseHistoricoLogs">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Histórico de operações anteriores ({{ $historicoLogs->count() }})
                                </button>
                            </h2>

                            <div id="collapseHistoricoLogs"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#accordionHistoricoLogs">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ação</th>
                                                    <th>Arquivo</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-end">Tamanho</th>
                                                    <th class="text-center">Duração</th>
                                                    <th>Mensagem</th>
                                                    <th class="text-center">Data</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($historicoLogs as $log)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $log->acao }}</strong>
                                                        </td>

                                                        <td>{{ $log->arquivo ?? '-' }}</td>

                                                        <td class="text-center">
                                                            @if($log->status === 'sucesso')
                                                                <span class="badge bg-success">Sucesso</span>
                                                            @elseif($log->status === 'erro')
                                                                <span class="badge bg-danger">Erro</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">Pendente</span>
                                                            @endif
                                                        </td>

                                                        <td class="text-end">
                                                            {{ number_format(($log->tamanho_bytes ?? 0) / 1024 / 1024, 2, ',', '.') }} MB
                                                        </td>

                                                        <td class="text-center">
                                                            {{ $log->duracao_ms ? number_format($log->duracao_ms / 1000, 2, ',', '.') . 's' : '-' }}
                                                        </td>

                                                        <td>
                                                            <small>{{ $log->mensagem ?? '-' }}</small>
                                                        </td>

                                                        <td class="text-center">
                                                            {{ optional($log->created_at)->format('d/m/Y H:i') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

</div>

<div class="modal fade" id="modalGerandoBackup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5 class="fw-bold mb-2">Gerando backup...</h5>
                <p class="text-muted mb-0">
                    Aguarde. O sistema está copiando banco de dados, arquivos e compactando o pacote.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('formGerarBackup')?.addEventListener('submit', function () {
        const modal = new bootstrap.Modal(document.getElementById('modalGerandoBackup'), {
            backdrop: 'static',
            keyboard: false
        });

        modal.show();
    });
</script>
@endsection