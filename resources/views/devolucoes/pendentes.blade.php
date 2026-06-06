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
                    <strong>Lote:</strong> {{ $devolucao->itemVenda->lote->numero_lote ?? 'Sem lote' }} <br>
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
                                <!-- 🔥 CORREÇÃO: Mudado de 'storage/' para 'imgDevolucoes/' -->
                                <img src="{{ asset('imgDevolucoes/' . $imagem) }}" 
                                    class="img-zoom"
                                    alt="Imagem {{ $idx + 1 }}" 
                                    style="width: {{ $tamanho }}px; height: {{ $tamanho }}px; object-fit: cover; border-radius: 5px;">

                            @empty
                                <span class="text-muted fs-7">Sem imagem</span>
                            @endforelse
                        </div>
              

                        <div class="col-md-8">
                            <strong>Motivo da Devolução:</strong>
                            <p>{{ $devolucao->motivo }}</p>
                        </div>
                         <!-- Alinhamento dos botões principais originais -->
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <!-- Formulário estruturado como um item flex direto -->
                            <form action="{{ route('devolucoes.aprovar', $devolucao->id) }}" method="POST" class="m-0 p-0 d-flex align-items-center">
                                @csrf
                                @method('PUT')
                                <button type="submit" id="btn-aprovar-{{ $devolucao->id }}" class="btn btn-success btn-sm" disabled>
                                    Aprovar
                                </button>
                            </form>

                            <!-- Botão Rejeitar -->
                            <button type="button" class="btn btn-warning btn-sm btn-rejeitar-trigger text-dark" data-id="{{ $devolucao->id }}">
                                Rejeitar
                            </button>

                            <!-- Link Gerar Vale-Troca estruturado como flex para bater a altura -->
                            <a href="{{ route('devolucoes.cupom', $devolucao) }}" class="btn btn-primary btn-sm gerar-vale d-flex align-items-center justify-content-center" data-id="{{ $devolucao->id }}" target="_blank">
                                Gerar Vale-Troca
                            </a>
                        </div>

                    <!-- 🔥 SEÇÃO DE REJEIÇÃO: Adicionada abaixo do bloco de botões -->
                    <div id="secao-rejeitar-{{ $devolucao->id }}" class="card p-3 bg-light border-warning mt-3 {{ ($errors->has('motivo_rejeicao') || $errors->has('observacao')) && old('rejeicao_id') == $devolucao->id ? '' : 'd-none' }}">
                        <h6 class="text-danger fw-bold mb-3">Registrar Rejeição do Item</h6>
                        
                        <form action="{{ route('devolucoes.rejeitar', $devolucao->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <input type="hidden" name="rejeicao_id" value="{{ $devolucao->id }}">

                            <!-- Campo 1: Motivo da Rejeição -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Motivo da Rejeição <span class="text-danger">*</span></label>
                                <input type="text" 
                                    name="motivo_rejeicao" 
                                    class="form-control form-control-sm @error('motivo_rejeicao') is-invalid @enderror" 
                                    value="{{ old('rejeicao_id') == $devolucao->id ? old('motivo_rejeicao') : '' }}" 
                                    placeholder="Ex: Item violado ou fora do prazo de garantia">
                                @error('motivo_rejeicao')
                                    @if(old('rejeicao_id') == $devolucao->id)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @endif
                                @enderror
                            </div>

                            <!-- Campo 2: Observação -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-1">Observação <span class="text-danger">*</span></label>
                                <textarea name="observacao" 
                                        rows="2" 
                                        class="form-control form-control-sm @error('observacao') is-invalid @enderror" 
                                        placeholder="Insira detalhes complementares da recusa...">{{ old('rejeicao_id') == $devolucao->id ? old('observacao') : '' }}</textarea>
                                @error('observacao')
                                    @if(old('rejeicao_id') == $devolucao->id)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @endif
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger btn-sm">Confirmar Rejeição Comercial</button>
                                <button type="button" class="btn btn-secondary btn-sm btn-cancelar-rejeitar" data-id="{{ $devolucao->id }}">Cancelar</button>
                            </div>
                        </form>
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
        
        // 1. VALE-TROCA: Habilita o botão Aprovar do card correto
        document.querySelectorAll('.gerar-vale').forEach(btn => {
            btn.addEventListener('click', function (e) {
                let id = this.dataset.id;
                let btnAprovar = document.getElementById('btn-aprovar-' + id);
                if (btnAprovar) {
                    btnAprovar.removeAttribute('disabled');
                    btnAprovar.classList.remove('btn-secondary');
                    btnAprovar.classList.add('btn-success');
                }
            });
        });

        // 2. EXIBIR REJEIÇÃO: Abre o painel de inputs do card correto
        document.querySelectorAll('.btn-rejeitar-trigger').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                let secao = document.getElementById('secao-rejeitar-' + id);
                if (secao) {
                    secao.classList.remove('d-none');
                    secao.querySelector('input[name="motivo_rejeicao"]').focus();
                }
            });
        });

        // 3. CANCELAR REJEIÇÃO: Oculta o painel e limpa os campos
        document.querySelectorAll('.btn-cancelar-rejeitar').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                let secao = document.getElementById('secao-rejeitar-' + id);
                if (secao) {
                    secao.classList.add('d-none');
                    secao.querySelector('input[name="motivo_rejeicao"]').value = '';
                    secao.querySelector('textarea[name="observacao"]').value = '';
                }
            });
        });

        // 4. ZOOM DAS IMAGENS: Controla o estado ativo nos cliques
        const imagens = document.querySelectorAll('.img-zoom');
        imagens.forEach(img => {
            img.addEventListener('click', function(event) {
                event.stopPropagation();
                this.classList.toggle('active');
            });
        });

        document.addEventListener('click', function() {
            imagens.forEach(img => img.classList.remove('active'));
        });
    });
</script>



