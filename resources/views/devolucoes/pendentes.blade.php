@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Devoluções Pendentes</h2>
    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2 mb-2">
            <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
    @forelse($devolucoes as $devolucao)
        @if($loop->index % 2 == 0)
            <div class="row mb-4">
        @endif
         
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <strong>Venda ID:</strong> 000{{ optional($devolucao->vendaItem->venda->cliente)->id ?? '-' }} <br>
                    <strong>Cliente:</strong> {{ optional($devolucao->vendaItem->venda->cliente)->nome ?? '-' }} <br>
                    <strong>Lote:</strong> {{ optional($devolucao->vendaItem->lote)->id ?? '-' }} <br>
                    <strong>Codigo Produto:</strong> 000{{ optional($devolucao->produto)->id ?? '-' }} <br>
                    <strong>Descrição:</strong> {{ optional($devolucao->vendaItem->produto)->nome ?? '-' }} <br>
                    <strong>Qtde Devolvida:</strong> {{ $devolucao->quantidade }} <br>
                    <strong>Status:</strong> <span class="badge bg-warning">{{ ucfirst($devolucao->status) }}</span>
                </div>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            @if(optional($devolucao->vendaItem->produto)->imagem)
                                <img src="{{ asset('storage/' . $devolucao->vendaItem->produto->imagem) }}" class="img-fluid rounded" alt="Produto">
                            @else
                                <img src="{{ asset('images/no-image.png') }}" class="img-fluid rounded" alt="Sem imagem">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <strong>Motivo da Devolução:</strong>
                            <p>{{ $devolucao->motivo }}</p>

                            <strong>Logs da Devolução:</strong>
                            <ul class="list-group list-group-flush mb-2">
                                @foreach(optional($devolucao->logs) as $log)
                                    <li class="list-group-item">
                                        <small>
                                            <strong>{{ ucfirst($log->acao) }}</strong> - {{ $log->descricao }} 
                                            ({{ $log->usuario }} em {{ $log->created_at->format('d/m/Y H:i') }})
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                                                                <form action="{{ route('devolucoes.aprovar', $devolucao->id) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            id="btn-aprovar-{{ $devolucao->id }}"
                                            class="btn btn-success btn-sm"
                                            disabled>
                                        Aprovar
                                    </button>
                                </form>

                                <form action="{{ route('devolucoes.rejeitar', $devolucao->id) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        Rejeitar
                                    </button>
                                </form>

                                <a href="{{ route('devolucoes.cupom', $devolucao) }}"
                                class="btn btn-primary btn-sm gerar-vale"
                                data-id="{{ $devolucao->id }}"
                                target="_blank">
                                    Gerar Vale-Troca
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($loop->index % 2 == 1 || $loop->last)
            </div>
        @endif
    @empty
        <div class="alert alert-info">
            Nenhuma devolução pendente encontrada.
        </div>
    @endforelse

    <div class="d-flex justify-content-center">
        {{ $devolucoes->links() }}
    </div>
</div>
@endsection
<script>
document.addEventListener("DOMContentLoaded", function() {

    // Quando clicar no botão "Gerar Vale-Troca"
    document.querySelectorAll('.gerar-vale').forEach(btn => {

        btn.addEventListener('click', function() {

            let id = this.getAttribute('data-id');

            // Habilita o botão Aprovar correspondente
            let btnAprovar = document.getElementById('btn-aprovar-' + id);
            if (btnAprovar) {
                btnAprovar.removeAttribute('disabled');
                btnAprovar.classList.add('btn-success');
            }
        });

    });

});
</script>

