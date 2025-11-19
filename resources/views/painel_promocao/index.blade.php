@extends('layouts.app')

@section('content')
<div class="container">

    {{-- ===================================================== --}}
    {{-- CARD: Promoções Vigentes --}}
    {{-- ===================================================== --}}
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
                <h5 class="mb-0">Promoções Vigentes (Hoje)</h5>
                <a href="{{ route('promocoes.create') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Nova Promoção
                </a>
            </div>

            <div class="card-body">

                @if($promocoesAtivas->isEmpty())
                    <p class="text-muted mb-0">Nenhuma promoção ativa hoje.</p>

                @else
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        @foreach($promocoesAtivas as $promo)
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body">

                                        {{-- Título + Status --}}
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-title mb-1">
                                                {{ $promo->produto->nome ?? ($promo->categoria->nome ?? '—') }}
                                            </h6>
                                            <span class="badge bg-success align-self-start">Ativa</span>
                                        </div>

                                        <p class="mb-1 small text-muted">
                                            {{ 'Produto ID: '. $promo->produto_id }}
                                        </p>

                                        {{-- Informações --}}
                                        <dl class="row mb-2 small">

                                            <dt class="col-5">Início</dt>
                                            <dd class="col-7">
                                                {{ $promo->promocao_inicio ? \Carbon\Carbon::parse($promo->promocao_inicio)->format('d/m/Y') : '—' }}
                                            </dd>

                                            <dt class="col-5">Fim</dt>
                                            <dd class="col-7">
                                                {{ $promo->promocao_fim ? \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') : '—' }}
                                            </dd>

                                            <dt class="col-5">Tipo desconto</dt>
                                            <dd class="col-7">
                                                @if(!empty($promo->desconto_percentual) && $promo->desconto_percentual > 0)
                                                    Percentual ({{ number_format($promo->desconto_percentual, 2, ',', '.') }}%)
                                                @elseif(!empty($promo->preco_promocional) && $promo->preco_promocional > 0)
                                                    Valor Fixo
                                                @elseif(!empty($promo->acrescimo_percentual) && $promo->acrescimo_percentual > 0)
                                                    Acréscimo ({{ number_format($promo->acrescimo_percentual, 2, ',', '.') }}%)
                                                @elseif(!empty($promo->acrescimo_valor) && $promo->acrescimo_valor > 0)
                                                    Acréscimo R$ ({{ number_format($promo->acrescimo_valor, 2, ',', '.') }})
                                                @else
                                                    —
                                                @endif
                                            </dd>

                                            <dt class="col-5">Estoque</dt>
                                            <dd class="col-7">
                                                {{ isset($promo->quantidade_estoque)
                                                    ? $promo->quantidade_estoque
                                                    : (isset($promo->produto->quantidade_estoque)
                                                        ? $promo->produto->quantidade_estoque
                                                        : '—') }}
                                            </dd>

                                            <dt class="col-5">Preço original</dt>
                                            <dd class="col-7">
                                                {{ isset($promo->preco_original)
                                                    ? 'R$ '.number_format($promo->preco_original,2,',','.')
                                                    : (isset($promo->produto->preco)
                                                        ? 'R$ '.number_format($promo->produto->preco,2,',','.')
                                                        : '—') }}
                                            </dd>

                                            <dt class="col-5">Preço promoção</dt>
                                            <dd class="col-7">
                                                @if(isset($promo->preco_promocional) && $promo->preco_promocional > 0)
                                                    R$ {{ number_format($promo->preco_promocional, 2, ',', '.') }}
                                                @elseif(isset($promo->preco_final))
                                                    R$ {{ number_format($promo->preco_final, 2, ',', '.') }}
                                                @elseif(!empty($promo->desconto_percentual) && isset($promo->produto->preco))
                                                    R$ {{ number_format($promo->produto->preco * (1 - $promo->desconto_percentual/100), 2, ',', '.') }}
                                                @else
                                                    —
                                                @endif
                                            </dd>
                                        </dl>

                                        {{-- Rodapé --}}
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">Criado: {{ $promo->created_at->format('d/m/Y H:i') }}</small>
                                            <div>
                                                <a href="{{ route('promocoes.show', $promo->id) }}" class="btn btn-outline-primary btn-sm">Ver</a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @endif
            </div>
        </div>
    </div>



    {{-- ===================================================== --}}
    {{-- CARD: Promoções Encerradas — padronizado igual ao Vigentes --}}
    {{-- ===================================================== --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Promoções Encerradas (mês atual)</h5>
            </div>

            <div class="card-body">
                @if($promocoesEncerradas->isEmpty())
                    <p class="text-muted mb-0">Nenhuma promoção encerrada neste mês.</p>

                @else
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        @foreach($promocoesEncerradas as $promo)
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body">

                                        {{-- Cabeçalho --}}
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-title mb-1">
                                                {{ $promo->produto->nome ?? ($promo->categoria->nome ?? '—') }}
                                            </h6>
                                            <span class="badge bg-secondary">Encerrada</span>
                                        </div>

                                        <p class="mb-1 small text-muted">
                                            <!-- {{ $promo->tipo_abrangencia === 'produto' ? 'Por produto' : ($promo->tipo_abrangencia === 'categoria' ? 'Por categoria' : 'Geral') }} -->
                                              Produto ID: {{$promo->produto_id}}
                                        </p>

                                        {{-- Lista padronizada --}}
                                        <dl class="row mb-2 small">

                                            <dt class="col-5">Início</dt>
                                            <dd class="col-7">
                                                {{ $promo->promocao_inicio ? \Carbon\Carbon::parse($promo->promocao_inicio)->format('d/m/Y') : '—' }}
                                            </dd>

                                            <dt class="col-5">Fim</dt>
                                            <dd class="col-7">
                                                {{ $promo->promocao_fim ? \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') : '—' }}
                                            </dd>

                                            <dt class="col-5">Tipo desconto</dt>
                                            <dd class="col-7">
                                                @if(!empty($promo->desconto_percentual) && $promo->desconto_percentual > 0)
                                                    Percentual ({{ number_format($promo->desconto_percentual, 2, ',', '.') }}%)
                                                @elseif(!empty($promo->preco_promocional) && $promo->preco_promocional > 0)
                                                    Valor fixo
                                                @else
                                                    —
                                                @endif
                                            </dd>

                                            <dt class="col-5">Estoque</dt>
                                            <dd class="col-7">
                                                {{ isset($promo->quantidade_estoque)
                                                    ? $promo->quantidade_estoque
                                                    : (isset($promo->produto->quantidade_estoque)
                                                        ? $promo->produto->quantidade_estoque
                                                        : '—') }}
                                            </dd>

                                            <dt class="col-5">Preço original</dt>
                                            <dd class="col-7">
                                                {{ isset($promo->preco_original)
                                                    ? 'R$ '.number_format($promo->preco_original,2,',','.')
                                                    : (isset($promo->produto->preco)
                                                        ? 'R$ '.number_format($promo->produto->preco,2,',','.')
                                                        : '—') }}
                                            </dd>

                                            <dt class="col-5">Preço promoção</dt>
                                            <dd class="col-7">
                                                {{ isset($promo->preco_promocional) && $promo->preco_promocional > 0
                                                    ? 'R$ '.number_format($promo->preco_promocional,2,',','.')
                                                    : (isset($promo->preco_final)
                                                        ? 'R$ '.number_format($promo->preco_final,2,',','.')
                                                        : '—') }}
                                            </dd>
                                        </dl>

                                        {{-- Rodapé --}}
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">
                                                Encerrada em: 
                                                {{ $promo->promocao_fim ? \Carbon\Carbon::parse($promo->promocao_fim)->format('d/m/Y') : '—' }}
                                            </small>

                                            <a href="{{ route('promocoes.show', $promo->id) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                Ver
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
