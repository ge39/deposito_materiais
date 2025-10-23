@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Registrar Devolução / Troca - Venda #{{ $item->venda->id }}</h2>
    <h4 class="mb-4">Cliente: {{ $item->venda->cliente->nome }}</h4>

    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2 mb-2">
        <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

        @foreach($item->venda->itens as $itemVenda)
            @php 
                $qtdeMax = $itemVenda->quantidade - $itemVenda->quantidade_devolvida;
                $devolucoes = $itemVenda->devolucoes ?? collect();
                $jaDevolvido = $qtdeMax <= 0;
            @endphp
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm position-relative">
                    {{-- Carimbo de produto totalmente devolvido --}}
                    @if($jaDevolvido)
                        <div class="stamped">
                            PRODUTO JÁ DEVOLVIDO
                        </div>
                    @endif

                    <div class="card-header bg-light d-flex align-items-center gap-3">
                        <img src="{{ asset('storage/' . ($itemVenda->produto->imagem ?? '')) }}" alt="Produto" class="card-img-top" style="width: 80px; height:80px; object-fit: cover; border-radius: 8px;">
                        <strong>{{ $itemVenda->produto->nome }}</strong>
                    </div>

                    <div class="card-body">
                        <p><strong>Código:</strong> 000{{ $itemVenda->id }}</p>
                        <p><strong>Lote:</strong> 000{{ $itemVenda->lote_id }}</p>
                        <p><strong>Comprada:</strong> {{ $itemVenda->quantidade }}</p>
                        <p><strong>V. Unidade:</strong> R${{ number_format($itemVenda->preco_unitario, 2, ',', '.') }}</p>
                        <p><strong>Já Devolvida:</strong> {{ $itemVenda->quantidade_devolvida }}</p>
                        <p><strong>Data da Venda:</strong> {{ $itemVenda->venda->created_at->format('d/m/Y') }}</p>
                        <p><strong>Última Devolução:</strong> 
                            @if($devolucoes->count() > 0)
                                {{ $devolucoes->last()->created_at->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </p>

                        @if(!$jaDevolvido)
                        <form action="{{ route('devolucoes.salvar') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-2">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $itemVenda->id }}">

                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0">À Devolver:</label>
                                <input type="number" name="quantidade" class="form-control" min="1" max="{{ $qtdeMax }}" placeholder="0" required style="width: 100px;">
                            </div>

                            <input type="text" name="motivo" class="form-control" placeholder="Motivo da devolução" required>

                            <label class="mt-2">Evidências (opcional, até 4 imagens):</label>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                @for ($i = 1; $i <= 4; $i++)
                                    <div class="image-container">
                                        <input 
                                            type="file" 
                                            name="imagem{{ $i }}" 
                                            id="imagem-{{ $itemVenda->id }}-{{ $i }}" 
                                            class="image-input" 
                                            accept="image/*" 
                                            hidden
                                        >
                                        <label for="imagem-{{ $itemVenda->id }}-{{ $i }}" class="image-label">
                                            <img 
                                                id="preview-{{ $itemVenda->id }}-{{ $i }}" 
                                                class="img-preview" 
                                                src="" 
                                                alt="Adicionar imagem"
                                            >
                                        </label>
                                    </div>
                                @endfor
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-danger btn-sm flex-grow-1">Confirmar</button>
                                <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary btn-sm flex-grow-1">Voltar</a>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    // Pré-visualização das imagens
    document.querySelectorAll('.image-input').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            const previewId = 'preview-' + this.id.split('-')[1] + '-' + this.id.split('-')[2];
            const preview = document.getElementById(previewId);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.src = ev.target.result;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.classList.remove('has-image');
            }
        });
    });
</script>

<style>
    .image-container {
        position: relative;
        width: 100px;
        height: 100px;
    }

    .image-label {
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100px;
        height: 100px;
        background-color: #f8f9fa;
        border: 1px dashed #ccc;
        border-radius: 8px;
        transition: background 0.3s;
    }

    .image-label:hover {
        background-color: #e9ecef;
    }

    .img-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
        background-color: #f8f9fa;
    }

    .img-preview:not(.has-image)::before {
        content: "+";
        position: absolute;
        font-size: 2rem;
        color: #bbb;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Carimbo produto devolvido */
    .stamped {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-20deg);
        background: rgba(255, 0, 0, 0.7);
        color: white;
        font-weight: bold;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 1rem;
        z-index: 10;
        text-align: center;
    }
</style>
@endsection
