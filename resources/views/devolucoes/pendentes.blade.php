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
                    @php
                        $vendaItem = $devolucao->vendaItem;
                        $venda = optional($vendaItem)->venda;
                        $cliente = optional($venda)->cliente;
                        $produtoVendaItem = optional($vendaItem)->produto;
                        $lote = optional($vendaItem)->lote;
                    @endphp

                    <strong>Venda ID:</strong> 000{{$devolucao->venda_id?? '-' }} <br>
                    <strong>Cliente:</strong> {{ $devolucao->itemVenda->venda->cliente->nome ?? '' }} <br>
                    <strong>Lote:</strong> {{ $devolucao->itemVenda->lote->numero ?? 'Sem lote' }} <br>
                    <strong>Produto ID:</strong> 000{{ optional($devolucao->produto)->id ?? '-' }} <br>
                    <strong>Produto Nome:</strong> {{ $devolucao->produto->nome ?? 'Sem Descrição' }} <br>
                    <strong>Motivo Rejeição:</strong> {{ $devolucao->motivo_rejeicao }} <br>
                    <strong>Valor Unitario</strong> R$ {{ $devolucao->itemVenda->preco_unitario ?? '-' }} <br>
                    <strong>Subtotal</strong> R$ {{ number_format($devolucao->itemVenda->preco_unitario * $devolucao->itemVenda->quantidade, 2, ',', '.') }} <br>
                    <strong>Quantidade Comprada:</strong> {{ $devolucao->itemVenda->quantidade ?? 'Sem Descrição' }} <br>
                    <strong>Qtde Devolvida:</strong> {{ $devolucao->quantidade }} <br>
                    <strong>Qtde Restante:</strong> {{ $devolucao->itemVenda->quantidade - $devolucao->quantidade }}  <br>
                    <strong>Status:</strong> <span class="badge bg-warning">{{ ucfirst($devolucao->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-10 d-flex gap-2 flex-row flex-wrap align-items-start">

                            @php
                                $imagens = [];
                                for ($i = 1; $i <= 4; $i++) {
                                    $campo = 'imagem' . $i;
                                    if ($devolucao->$campo) {
                                        $imagens[] = $devolucao->$campo;
                                    }
                                }
                                $multiplicador = count($imagens) > 1 ? 0.99 : 1;
                                $tamanho = 90 * $multiplicador;
                            @endphp

                            @forelse($imagens as $idx => $imagem)
                                <img src="{{ asset('storage/' . $imagem) }}" 
                                    class="img-zoom"
                                    alt="Imagem {{ $idx + 1 }}" 
                                    style="width: {{ $tamanho }}px; height: {{ $tamanho }}px; object-fit: cover; border-radius: 5px;">

                            @empty
                                Sem imagem
                            @endforelse
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

        @if($loop->index % 2 == 1 || $loop->last)
            </div>
        @endif
    @empty
        <div class="alert alert-info">
            Nenhuma devolução pendente encontrada.
        </div>
    @endforelse

   
</div>
@endsection

<style>
    .img-zoom {
        transition: transform 0.25s ease-in-out;
        cursor: zoom-in;
        z-index: 10;
        position: relative;
    }

    .img-zoom.active {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%) scale(3); /* zoom + centro */
        cursor: zoom-out;
        z-index: 9999;
        max-width: 90%;
        max-height: 90%;
    }

    /* Impede que o card estoure quando o zoom está ativo */
    .zoom-container {
        position: relative;
        overflow: hidden; /* Impede sair do container */
    }
</style>



<script>
    document.addEventListener("DOMContentLoaded", function() {

        document.querySelectorAll('.gerar-vale').forEach(btn => {
            btn.addEventListener('click', function (e) {

                // OPÇÃO 1 – deixa abrir o PDF normalmente:
                // (não usa preventDefault)

                let id = this.dataset.id;
                let btnAprovar = document.getElementById('btn-aprovar-' + id);

                if (btnAprovar) {
                    btnAprovar.removeAttribute('disabled');  // ✔ corrigido
                    btnAprovar.classList.remove('btn-secondary');
                    btnAprovar.classList.add('btn-success');
                }
            });
        });

    });

    document.addEventListener("DOMContentLoaded", function() {

        // Seleciona todas as imagens das devoluções
        const imagens = document.querySelectorAll('.img-zoom');

        imagens.forEach(img => {
            img.addEventListener('click', function(event) {
                event.stopPropagation(); // impede que o clique saia da imagem
                this.classList.toggle('active'); // ativa o zoom
            });
        });

        // Clicar fora da imagem tira o zoom
        document.addEventListener('click', function() {
            imagens.forEach(img => img.classList.remove('active'));
        });

    });


</script>

