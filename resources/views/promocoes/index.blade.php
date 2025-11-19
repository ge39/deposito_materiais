@extends('layouts.app')

@section('content')
<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Promoções</h2>
        <a href="{{ route('promocoes.create') }}" class="btn btn-primary">Nova Promoção</a>
    </div>

    {{-- Mensagens --}}
   @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Se não existir promoções --}}
    @if ($promocoes->count() === 0)
        <div class="alert alert-info">Nenhuma promoção encontrada.</div>
    @else

        <div class="row">
            @foreach ($promocoes as $promo)
                <div class="col-md-4 mb-4">

                    <div class="card shadow-sm h-100">

                        <div class="card-body">

                            {{-- Nome Produto ou Categoria ou Geral --}}
                            <h5 class="card-title fw-bold">

                                @if($promo->tipo_abrangencia === 'produto')
                                    {{ $promo->produto->nome ?? 'Produto não encontrado' }}

                                @elseif($promo->tipo_abrangencia === 'categoria')
                                    Categoria: {{ $promo->categoria->nome ?? 'Categoria não encontrada' }}

                                @else
                                    Promoção Geral
                                @endif

                            </h5>

                            {{-- Tipo Abrangência --}}
                            <p class="text-muted mb-1">
                                Produto ID: <strong>{{ $promo->produto_id }}</strong>
                            </p>

                            <hr>

                            {{-- Preços --}}
                            <p class="mb-1">
                                Preço Original:
                                <strong>R$ {{ number_format($promo->preco_original, 2, ',', '.') }}</strong>
                            </p>
                             {{-- desconto --}}
                            <p class="mb-1">
                                Desconto Aplicado:
                                <strong>{{ $promo->desconto_percentual }} %</strong>
                            </p>
                            <p class="mb-2">
                                Preço Promocional:
                                <strong class="text-success">
                                    R$ {{ number_format($promo->preco_promocional, 2, ',', '.') }}
                                </strong>
                            </p>

                            {{-- Período --}}
                            <p class="mb-1">
                                Início: <strong>{{ \Carbon\Carbon::parse($promo->promocao_inicio)->format('d/m/Y') }}</strong>
                            </p>

                            <p>
                                Fim: <strong>{{ \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') }}</strong>
                            </p>

                            {{-- Status --}}
                            @php
                                $hoje = \Carbon\Carbon::today();
                                $expirada = $promo->promocao_fim < $hoje;
                            @endphp

                            @if(!$promo->status)
                                <span class="badge bg-secondary mb-3">Desativada</span>
                            @elseif($expirada)
                                <span class="badge bg-danger mb-3">Expirada</span>
                            @else
                                <span class="badge bg-success mb-3">Ativa</span>
                            @endif

                            <div class="d-flex justify-content-between mt-2">

                                {{-- Editar --}}
                                <a href="{{ route('promocoes.edit', $promo->id) }}" class="btn btn-sm btn-warning">
                                    Editar
                                </a>

                                {{-- Toggle Status --}}
                                <form action="{{ route('promocoes.toggle', $promo->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-info">
                                        {{ $promo->status ? 'Encerrar Promoção' : 'Ativar' }}
                                    </button>
                                </form>

                                {{-- Excluir --}}
                                <form action="{{ route('promocoes.destroy', $promo->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Excluir esta promoção?')">
                                        Excluir
                                    </button>
                                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
                                        Voltar
                                    </a> 
                                </form>

                            </div>

                        </div>

                    </div>

                </div>
            @endforeach
        </div>

        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $promocoes->links() }}
        </div>

    @endif

</div>
@endsection
