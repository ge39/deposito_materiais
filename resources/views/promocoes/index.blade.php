@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Promoções & Descontos</h4>
            <a href="{{ route('promocoes.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Nova Promoção
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($promocoes->isEmpty())
                <p class="text-muted text-center mt-3">Nenhuma promoção cadastrada.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th class="text-center" style="width: 180px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promocoes as $promocao)
                                <tr>
                                    <td>{{ $promocao->id }}</td>
                                    <td>{{ $promocao->produto->nome ?? '—' }}</td>
                                    <td>
                                        @if($promocao->tipo_desconto === 'percentual')
                                            Percentual
                                        @else
                                            Valor Fixo
                                        @endif
                                    </td>
                                    <td>
                                        @if($promocao->tipo_desconto === 'percentual')
                                            {{ $promocao->valor_desconto }}%
                                        @else
                                            R$ {{ number_format($promocao->valor_desconto, 2, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($promocao->data_inicio)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($promocao->data_fim)->format('d/m/Y') }}</td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <a href="{{ route('promocoes.show', $promocao->id) }}" class="btn btn-info btn-sm">
                                                Ver
                                            </a>
                                            <a href="{{ route('promocoes.edit', $promocao->id) }}" class="btn btn-warning btn-sm">
                                                Editar
                                            </a>
                                            <form action="{{ route('promocoes.destroy', $promocao->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta promoção?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $promocoes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
