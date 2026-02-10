@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- ALERTA PRINCIPAL --}}
    @if ($caixa->status === 'fechado')
        <div class="alert alert-success p-4 fs-5">
            <h3 class="alert-heading">✅ Caixa fechado com sucesso</h3>
            <p class="mb-0">
                O caixa foi encerrado corretamente, sem divergências.
            </p>
        </div>

    @elseif ($caixa->status === 'inconsistente')
        <div class="alert alert-warning p-4 fs-5">
            <h3 class="alert-heading">⚠️ Caixa fechado com inconsistências</h3>
            <p class="mb-0">
                Foram identificadas divergências.
                Este caixa será encaminhado para <strong>auditoria fiscal</strong>.
            </p>
        </div>

    @elseif ($caixa->status === 'fechamento_sem_movimento')
        <div class="alert alert-danger p-4 fs-5">
            <h3 class="alert-heading">🚫 Caixa fechado sem movimentação</h3>
            <p class="mb-0">
                O caixa foi encerrado sem registro de vendas.
            </p>
        </div>
    @endif

    {{-- CARD DE RESUMO --}}
    <div class="card shadow-lg">
        <div class="card-header fs-4">
            <strong>Resumo do Caixa</strong>
        </div>

        <div class="card-body fs-5">
            <ul class="list-group list-group-flush">

                <li class="list-group-item">
                    <strong>ID do Caixa:</strong> {{ $caixa->id }}
                </li>

                <li class="list-group-item">
                    <strong>Status:</strong>
                    <span class="badge fs-6 px-3 py-2
                        @if($caixa->status === 'fechado') bg-success
                        @elseif($caixa->status === 'inconsistente') bg-warning text-dark
                        @else bg-danger
                        @endif
                    ">
                        {{ ucfirst(str_replace('_',' ', $caixa->status)) }}
                    </span>
                </li>

                <li class="list-group-item">
                    <strong>Valor de Fechamento:</strong>
                    R$ {{ number_format($caixa->valor_fechamento, 2, ',', '.') }}
                </li>

                <li class="list-group-item">
                    <strong>Data de Fechamento:</strong>
                    {{ $caixa->data_fechamento?->format('d/m/Y H:i') }}
                </li>
            </ul>
        </div>

        {{-- AÇÕES --}}
        <div class="card-footer d-flex justify-content-end gap-3 p-3">

            <a href="{{ route('caixa.abrir') }}"
               class="btn btn-success btn-lg">
                🔁 Abrir novo caixa
            </a>

            <a href="{{ route('logout') }}"
               class="btn btn-outline-secondary btn-lg"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                🚪 Sair
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>

        </div>
    </div>

</div>
@endsection


