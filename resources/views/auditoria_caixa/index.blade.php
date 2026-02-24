@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Relatório de Auditoria de Caixa</h3>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Caixa</th>
                <th>Auditor</th>
                <th>Data</th>
                <th>Total Sistema</th>
                <th>Total Físico</th>
                <th>Diferença</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditorias as $auditoria)

                @php
                    $rowClass = match($auditoria->status) {
                        'concluida' => 'table-success',
                        'corrigida' => 'table-warning',
                        'inconsistente' => 'table-danger',
                        default => 'table-secondary'
                    };
                @endphp

                <tr class="{{ $rowClass }}">
                    <td>{{ $auditoria->codigo_auditoria }}</td>
                    <td>#{{ $auditoria->caixa_id }}</td>
                    <td>{{ $auditoria->usuario->name ?? '-' }}</td>
                    <td>{{ $auditoria->data_auditoria->format('d/m/Y H:i') }}</td>

                    <td>R$ {{ number_format($auditoria->total_sistema,2,',','.') }}</td>
                    <td>R$ {{ number_format($auditoria->total_fisico,2,',','.') }}</td>

                    <td class="fw-bold">
                        @if($auditoria->diferenca != 0)
                            <span class="text-danger">
                                R$ {{ number_format($auditoria->diferenca,2,',','.') }}
                            </span>
                        @else
                            <span class="text-success">R$ 0,00</span>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-dark text-uppercase">
                            {{ $auditoria->status }}
                        </span>
                    </td>

                    <td>
                        <a href="{{ route('auditoria_caixa.show',$auditoria->id) }}"
                           class="btn btn-sm btn-primary">
                           Ver Relatório
                        </a>
                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>

    {{ $auditorias->links() }}
</div>
@endsection