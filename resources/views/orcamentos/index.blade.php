@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Orçamentos</h2>
        <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">
            Novo Orçamento
        </a>
    </div>
    <form method="GET" class="mb-4">
        <div class="row g-2">

            <!-- FILTRO STATUS -->
            <div class="col-md-4">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- Todos os Status --</option>
                    <option value="Aguardando Aprovação" {{ request('status') == 'Aguardando Aprovação' ? 'selected' : '' }}>Aguardando Aprovação</option>
                    <option value="Aprovado" {{ request('status') == 'Aprovado' ? 'selected' : '' }}>Aprovado</option>
                    <option value="Expirado" {{ request('status') == 'Expirado' ? 'selected' : '' }}>Expirado</option>
                </select>
            </div>

            <!-- BUSCA POR CÓDIGO -->
            <div class="col-md-4">
                <label class="form-label fw-bold">Código do Orçamento</label>
                <input type="text" name="codigo_orcamento" class="form-control"
                    placeholder="Ex: 1025"
                    value="{{ request('codigo_orcamento') }}">
            </div>

            <!-- BOTÃO -->
             <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1  h-50">Buscar</button>
                <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary flex-grow-1  h-50">Limpar</a>
            </div>

        </div>
    </form>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">

         <div class="d-flex justify-content-center mt-3">
            <div class="d-inline-block">
                {{ $orcamentos->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Total</th>
                <th>Status</th>
                <th>Código</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orcamentos as $orcamento)
        <tr @if($orcamento->status === 'Expirado') class="text-danger" @endif>
            <td>{{ $orcamento->id }}</td>
            <td>{{ $orcamento->cliente->nome ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</td>
            <td>R$ {{ number_format($orcamento->total, 2, ',', '.') }}</td>
            <td>
                @if ($orcamento->status === 'Expirado')
                    <span class="badge bg-danger" style="font-size: 14px;">
                        Expirado
                    </span>
                @elseif ($orcamento->status === 'Aguardando aprovacao')
                    <span class="badge bg-warning text-dark" style="font-size: 14px;">
                        Aguardando aprovação
                    </span>
                @elseif ($orcamento->status === 'Aprovado')
                    <span class="badge bg-success" style="font-size: 14px;">
                        Aprovado
                    </span>
                @elseif ($orcamento->status === 'Cancelado')
                    <span class="badge bg-secondary" style="font-size: 14px;">
                        Cancelado
                    </span>
                @endif
            </td>

            <td>{{ $orcamento->codigo_orcamento }}</td>
            <td>
                <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-sm btn-warning">Editar</a>
                <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-primary" target="_blank">Gerar PDF</a>
                <a href="{{ route('orcamentos.whatsapp', $orcamento->id) }}" 
                    class="btn btn-success btn-sm" 
                    target="_blank">
                        Enviar WhatsApp
                </a>
            </td>
        </tr>
            @endforeach
            @if($orcamentos->isEmpty())
                <tr>
                    <td colspan="7" class="text-center text-muted fw-bold py-3 text-red-500">
                        Nenhum orçamento encontrado para os filtros informados.
                    </td>
                </tr>
            @endif

        </tbody>

    </table>
   
    <div class="d-flex justify-content-center mt-3">
        <div class="d-inline-block">
            {{ $orcamentos->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>
@endsection
