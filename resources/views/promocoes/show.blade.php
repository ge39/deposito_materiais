@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Detalhes da Promoção</h4>
            <a href="{{ route('promocoes.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Tipo de Abrangência:</strong><br>
                    {{ ucfirst($promocao->tipo_abrangencia) }}
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong><br>
                    @if($promocao->em_promocao)
                        <span class="badge bg-success">Ativa</span>
                    @else
                        <span class="badge bg-secondary">Inativa</span>
                    @endif
                </div>
            </div>

            @if($promocao->produto)
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Produto:</strong><br>
                    {{ $promocao->produto->nome }}
                </div>
            </div>
            @endif

            @if($promocao->categoria)
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Categoria:</strong><br>
                    {{ $promocao->categoria->nome }}
                </div>
            </div>
            @endif

            <hr>

            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Desconto (%):</strong><br>
                    {{ $promocao->desconto_percentual ?? '—' }}
                </div>
                <div class="col-md-3">
                    <strong>Acréscimo (%):</strong><br>
                    {{ $promocao->acrescimo_percentual ?? '—' }}
                </div>
                <div class="col-md-3">
                    <strong>Acréscimo (R$):</strong><br>
                    @if($promocao->acrescimo_valor)
                        R$ {{ number_format($promocao->acrescimo_valor, 2, ',', '.') }}
                    @else
                        —
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Preço Promocional:</strong><br>
                    @if($promocao->preco_promocional)
                        R$ {{ number_format($promocao->preco_promocional, 2, ',', '.') }}
                    @else
                        —
                    @endif
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Início:</strong><br>
                    {{ $promocao->promocao_inicio ? \Carbon\Carbon::parse($promocao->promocao_inicio)->format('d/m/Y') : '—' }}
                </div>
                <div class="col-md-6">
                    <strong>Fim:</strong><br>
                    {{ $promocao->promocao_fim ? \Carbon\Carbon::parse($promocao->promocao_fim)->format('d/m/Y') : '—' }}
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between flex-wrap gap-2 mt-4">
                <div>
                    <a href="{{ route('promocoes.edit', $promocao->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form action="{{ route('promocoes.destroy', $promocao->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta promoção?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                </div>

                <form action="{{ route('promocoes.toggle', $promocao->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn {{ $promocao->em_promocao ? 'btn-secondary' : 'btn-success' }}">
                        @if($promocao->em_promocao)
                            <i class="bi bi-pause-circle"></i> Desativar Promoção
                        @else
                            <i class="bi bi-play-circle"></i> Ativar Promoção
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
