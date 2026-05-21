@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Painel de Auditoria de Caixas Encerrados</h3>
            <small class="text-muted">Acompanhamento e homologação fiscal de divergências de caixas</small>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark small text-uppercase">
                        <tr>
                            <th class="ps-3">Código Auditoria</th>
                            <th>Caixa ID</th>
                            <th>Auditor / Fiscal</th>
                            <th>Data Fechamento</th>
                            <th class="text-end">Total Sistema</th>
                            <th class="text-end">Total Físico</th>
                            <th class="text-end">Divergência</th>
                            <th class="text-center">Status Fiscal</th>
                            <th class="text-center pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $auditoria)
                            @php
                                // Define a cor de fundo da linha com base no status cadastrado no banco
                                $rowClass = match($auditoria->status) {
                                    'concluida'     => 'table-success-subtle table-success', // Verde suave para caixas perfeitos
                                    'corrigida'     => 'table-warning-subtle table-warning', // Amarelo para caixas ajustados pelo fiscal
                                    'inconsistente' => 'table-danger-subtle table-danger',   // Vermelho para quebras de caixa ativas
                                    default         => 'table-secondary'
                                };
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td class="ps-3 fw-semibold">{{ $auditoria->codigo_auditoria }}</td>
                                <td class="fw-bold">#{{ $auditoria->caixa_id }}</td>
                                
                                {{-- 🌟 CORREGIDO: Exibe o nome do auditor vindo do JOIN ou exibe o ID de registro --}}
                                <td>{{ $auditoria->auditor_nome ?? $auditoria->usuario->name ?? 'Operador ID #' . $auditoria->user_id }}</td>
                                
                                {{-- 🌟 CORREGIDO: Tratamento dinâmico que impede o erro fatal de string --}}
                                <td>
                                    {{ $auditoria->data_auditoria instanceof \Carbon\Carbon 
                                        ? $auditoria->data_auditoria->format('d/m/Y H:i') 
                                        : \Carbon\Carbon::parse($auditoria->data_auditoria)->format('d/m/Y H:i') 
                                    }}
                                </td>

                                <td class="text-end text-muted">R$ {{ number_format($auditoria->total_sistema, 2, ',', '.') }}</td>
                                <td class="text-end fw-semibold">R$ {{ number_format($auditoria->total_fisico, 2, ',', '.') }}</td>

                                <td class="text-end fw-bold">
                                    @if((float)$auditoria->diferenca != 0)
                                        <span class="{{ $auditoria->diferenca < 0 ? 'text-danger' : 'text-primary' }}">
                                            R$ {{ number_format($auditoria->diferenca, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-success">R$ 0,00</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-dark text-uppercase font-monospace px-2 py-1">
                                        {{ $auditoria->status }}
                                    </span>
                                </td>

                                <td class="text-center pe-3">
                                   {{-- 🌟 CORREGIDO: Alterado para o padrão físico real de hífen do seu Laravel --}}
                                    <a href="/auditoria-caixa/{{ $auditoria->id }}" class="btn btn-sm btn-primary fw-bold px-3">
                                        🔍 Ver Relatório
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">Nenhum registro de auditoria arquivado no sistema.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Renderização da paginação padrão do Laravel nas extremidades --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $auditorias->links() }}
    </div>
</div>
@endsection
