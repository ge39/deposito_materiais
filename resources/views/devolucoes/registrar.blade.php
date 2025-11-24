@extends('layouts.app')

@section('content')

<div class="container">

            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

    <h2 class="mb-3">Registrar Devolução / Troca - Venda #{{ $venda->id }}</h2>
    <h4 class="mb-0">Cliente: {{ $venda->cliente->nome }}</h4>

    {{-- CAMPOS SOLICITADOS --}}
    <p class="mb-0"><strong>Total da Venda:</strong> 
        R$ {{ number_format($venda->total, 2, ',', '.') }}
    </p>

    <p class="mb-4"><strong>Data da Venda:</strong> 
        {{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}
    </p>
    {{-- FIM DO TRECHO ADICIONADO --}}

    <!-- <div class="d-flex justify-content-end mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Voltar</a>
    </div> -->

    <div class="row">

        @foreach($venda->itens as $itemVenda)

            @php
                $qtdDevolvida = $itemVenda->devolucoes
                    ->whereIn('status', ['aprovada', 'concluida'])
                    ->sum('quantidade');

                $qtdDisponivel = $itemVenda->quantidade - $qtdDevolvida;

                $valorExtornado = $qtdDevolvida * $itemVenda->preco_unitario;

                $jaDevolvido = $qtdDisponivel <= 0;

                $devolucoes = $itemVenda->devolucoes ?? collect();
            @endphp

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm position-relative compact-card">

                    @if($jaDevolvido)
                        <div class="stamped">PRODUTO JÁ DEVOLVIDO</div>
                    @endif

                    <div class="card-body">

                        {{-- Cabeçalho compacto com imagem à esquerda --}}
                        <div class="d-flex align-items-center mb-3">
                            <img 
                                src="{{ asset('storage/' . ($itemVenda->produto->imagem ?? '')) }}"
                                class="product-img"
                            />
                            <div class="ms-3">
                                <strong class="product-name">{{ $itemVenda->produto->nome }}</strong>
                                <div class="small text-muted">Produto ID: 000{{ $itemVenda->produto_id }}</div>
                            </div>
                        </div>

                        {{-- Bloco de informações --}}
                        <div class="info-block mb-3">
                            <div><strong>Lote:</strong> 000{{ $itemVenda->lote_id }}</div>
                            <div><strong>Qtde Comprada:</strong> {{ $itemVenda->quantidade }}</div> 
                            <div><strong>Valor Compra:</strong> R$ {{ number_format($itemVenda->subtotal, 2, ',', '.') }}</div>
                            <div><strong>Devolvida:</strong> {{ $qtdDevolvida }} {{ $itemVenda->produto->unidadeMedida->sigla }}</div>
                            
                           <div><strong>Preco Unit.:</strong> {{ $itemVenda->preco_unitario }}</div>
                           <div><strong>Disponível:</strong> {{ $qtdDisponivel }}</div>
                           <div><strong>Data da Venda:</strong> {{ $itemVenda->venda->created_at->format('d/m/Y') }}</div>
                           <div><strong>Valor Extornado:</strong> R$ {{ number_format($valorExtornado, 2, ',', '.') }}</div>
                            
                           
                            <div><strong>Última Devolução:</strong>
                                @if($devolucoes->count() > 0)
                                    {{ $devolucoes->last()->created_at->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>

                        {{-- Formulário compacto --}}
                        @if(!$jaDevolvido)
                        <form 
                            action="{{ route('devolucoes.salvar') }}" 
                            method="POST" 
                            enctype="multipart/form-data"
                            class="form-compact"
                        >
                            @csrf

                            <input type="hidden" name="item_id" value="{{ $itemVenda->id }}">

                            <div class="row g-2">

                                <div class="col-4">
                                    <label class="form-label small">À Devolver</label>
                                    <input 
                                        type="number" 
                                        name="quantidade" 
                                        min="1" 
                                        max="{{ $qtdDisponivel }}"
                                        class="form-control form-control-sm"
                                        required
                                    >
                                </div>

                                {{-- MOTIVO ATUALIZADO — SELECT2 + ARRAY FIXO --}}
                                <div class="col-8">
                                    <label class="form-label small">Motivo</label>
                                    <select 
                                        name="motivo" 
                                        class="form-control form-control-sm motivo-select"
                                    ></select>

                                    {{-- Campo outro motivo --}}
                                    <input 
                                        type="text"
                                        name="motivo_outro"
                                        class="form-control form-control-sm mt-1 d-none outro-motivo-input"
                                        placeholder="Descreva o motivo"
                                    >
                                </div>
                            </div>

                            {{-- Upload --}}
                            <label class="mt-2 small">Evidências (até 4 imagens)</label>

                            <div class="d-flex flex-wrap gap-2 mb-2">

                                @for($i = 1; $i <= 4; $i++)
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
                                                alt=""
                                            >
                                        </label>
                                    </div>
                                @endfor

                            </div>

                            
                            <div class="mt-2 d-flex gap-2 align-items-start">
                                <button class="btn btn-sm mt-3 btn-danger">Confirmar</button>
                                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm mt-3">Voltar</a>
                            </div>
                        </form>
                        @endif

                    </div>
                </div>
            </div>

        @endforeach

    </div>
</div>

{{-- Preview Script --}}
<script>
document.querySelectorAll('.image-input').forEach(input => {
    input.addEventListener('change', function() {
        const file = this.files[0];
        const previewId = 'preview-' + this.id.split('-')[1] + '-' + this.id.split('-')[2];
        const preview = document.getElementById(previewId);

        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
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

{{-- Select2 + Motivos --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const motivos = [
    "Atraso na obra",
    "Bloqueio no acesso à obra",
    "Carga incompleta na separação",
    "Cliente comprou a mais",
    "Cliente mudou de ideia",
    "Cliente recusou a receber",
    "Cor ou tonalidade divergente",
    "Defeito de fabricação",
    "Descrição incorreta do produto",
    "Desistência após orçamento",
    "Embalagem danificada",
    "Entrega fora do prazo",
    "Erro de cadastro no sistema",
    "Erro na conferência da mercadoria",
    "Erro na separação do pedido",
    "Erro no lançamento da venda",
    "Estoque desatualizado",
    "Fornecedor enviou produto errado",
    "Item faltando no pedido",
    "Material com defeito",
    "Material incompatível com o projeto",
    "Medida ou especificação incorreta",
    "Pedido duplicado",
    "Perda de material na obra",
    "Preço divergente na compra",
    "Problema no transporte",
    "Produto avariado no transporte",
    "Produto quebrado ou avariado",
    "Produto diferente do solicitado",
    "Produto excedente na obra",
    "Produto não serviu para a obra",
    "Quantidade incorreta",
    "Tamanho ou medida incompatível",
    "Troca por preferência do cliente",
    "Variação de lote não aceita",
    "Vencimento próximo do material",
];

    document.querySelectorAll('.motivo-select').forEach(select => {
        motivos.forEach(m => {
            let op = new Option(m, m, false, false);
            select.appendChild(op);
        });

        $(select).select2({
            placeholder: "Selecione ou digite o motivo",
            allowClear: true,
            width: 'resolve'
        });
    });

});
</script>

{{-- CSS --}}
<style>
.compact-card {
    border-radius: 10px;
    padding: 10px;
}

.product-img {
    width: 100px; 
    height: 100px; 
    object-fit: cover; 
    border-radius: 8px;
    border: 1px solid #ddd;
}

.product-name {
    font-size: 1.1rem;
}

.info-block {
    font-size: 0.85rem;
    line-height: 1.2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 15px;
}

.form-compact .form-label {
    margin-bottom: 2px;
}

.image-container {
    width: 70px;
    height: 70px;
    position: relative;
}

.image-label {
    width: 100%;
    height: 100%;
    border: 1px dashed #ccc;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    cursor: pointer;
}

.img-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.img-preview:not(.has-image)::before {
    content: "+";
    font-size: 1.6rem;
    color: #aaa;
    position: absolute;
}

.stamped {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-15deg);
    background: rgba(255, 0, 0, 0.75);
    color: white;
    font-weight: bold;
    padding: 6px 15px;
    border-radius: 6px;
    font-size: 0.9rem;
    z-index: 20;
}
</style>

@endsection
