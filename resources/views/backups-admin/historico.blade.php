@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Histórico de Backups</h3>
            <p class="text-muted mb-0">
                Geração, recuperação e integridade das cópias.
            </p>
        </div>
        <a href="{{ route('backups.index') }}"
           class="btn btn-outline-secondary">
            Backup Manual
        </a>
    </div>

    <div class="alert alert-info small">
        O resultado da geração local é independente
        do resultado da segunda cópia.
        Registros antigos podem não possuir identificação de origem.
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Ação</th>
                            <th>Origem</th>
                            <th>Status</th>
                            <th>Arquivo ZIP</th>
                            <th>Horário programado</th>
                            <th>Cópia principal</th>
                            <th>Segunda cópia</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>

                                <td class="text-nowrap">
                                    {{ $log->created_at?->format('d/m/Y H:i:s') }}
                                </td>

                                <td class="small">
                                    {{ $log->acao ?? '-' }}
                                </td>

                                <td>
                                    @if($log->historico_origem === 'Automático')
                                        <span class="badge bg-primary">
                                            Automático
                                        </span>
                                    @elseif($log->historico_origem === 'Recuperação')
                                        <span class="badge bg-warning text-dark">
                                            Recuperação
                                        </span>
                                    @else
                                        <span class="text-muted small">
                                            Não identificada
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @php
                                        $status = strtolower(
                                            (string) ($log->status ?? '')
                                        );
                                    @endphp

                                    @if($status === 'sucesso')
                                        <span class="badge bg-success">
                                            Sucesso
                                        </span>
                                    @elseif($status === 'pendente')
                                        <span class="badge bg-warning text-dark">
                                            Pendente
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            {{ $log->status ?? 'Erro' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="small">
                                    {{ $log->historico_zip }}
                                </td>

                                <td class="small text-nowrap">
                                    @forelse($log->historico_horarios as $horario)
                                        <div>{{ $horario }}</div>
                                    @empty
                                        -
                                    @endforelse
                                </td>

                                <td>
                                    {{ $log->historico_principal }}
                                </td>

                                <td>
                                    @if($log->historico_secundaria === 'Validada')
                                        <span class="badge bg-success">
                                            Validada
                                        </span>
                                    @elseif($log->historico_secundaria === 'Pendente')
                                        <span class="badge bg-warning text-dark">
                                            Pendente
                                        </span>
                                    @elseif($log->historico_secundaria === 'Divergente')
                                        <span class="badge bg-danger">
                                            Divergente
                                        </span>
                                    @else
                                        <span class="small text-muted">
                                            {{ $log->historico_secundaria }}
                                        </span>
                                    @endif
                                </td>

                                <td class="small">
                                    {{ $log->mensagem ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10"
                                    class="text-center text-muted py-4">
                                    Nenhum registro localizado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection