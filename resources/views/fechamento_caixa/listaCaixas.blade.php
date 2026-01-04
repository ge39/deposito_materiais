@extends('layouts.app') <!-- Ajuste para seu layout principal -->

@section('title', 'Fechamento de Caixa')

@section('content')
<div class="container">
    <h1 class="mb-4">Caixas Abertos / Inconsistentes</h1>

    @if($caixas->isEmpty())
        <div class="alert alert-info">Não há caixas abertos ou inconsistentes no momento.</div>
    @else
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID (Caixa)</th>
                    <th>Operador</th>
                    <th>Terminal ID</th>
                    <th>Abertura</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($caixas as $caixa)
                <tr>
                    <td>{{ $caixa->id }}</td>
                    <td>{{ $caixa->usuario->name }}</td>
                    <td>{{ $caixa->terminal_id ?? 'N/A' }}</td>
                    <td>{{ $caixa->data_abertura->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($caixa->status) }}</td>
                    <td>
                        <a href="{{ route('fechamento.auditar', $caixa->id) }}" class="btn btn-primary btn-sm">
                            Auditar / Fechar
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
