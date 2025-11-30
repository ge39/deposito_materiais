@extends('layouts.app')

@section('content')
<div class="container">

        {{-- AVISO FIXO: atenção quantidade pedida x entregue --}}
    <div class="alert alert-info small mt-2">
        <strong>Atenção:</strong> Ao confirmar o recebimento, verifique sempre a <em>Quantidade Pedida</em> e a <em>Quantidade Recebida</em>.
        Se a quantidade entregue for inferior à pedida, revise os itens antes de confirmar.
    </div>


    <h3 class="mb-3">Recebimento do Pedido #{{ $pedido->id }}</h3>

    <div class="mb-3">
        <p><strong>Fornecedor:</strong> {{ $pedido->fornecedor->nome }}</p>
        <p><strong>Data do Pedido:</strong> {{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y') }}</p>
    </div>

    @php
        $dataPadrao = now()->addDays(30)->format('Y-m-d');
        $dataMinima = now()->format('Y-m-d');
    @endphp

    <form action="{{ route('pedidos.receber', $pedido->id) }}" method="POST">
        @csrf

        <div class="d-flex flex-column gap-3">
            @foreach($pedido->itens as $item)

                <div class="p-2 border rounded item-card bg-white" 
                    data-item="{{ $item->id }}" 
                    style="transition: .25s; border-width:2px !important;">

                    {{-- TÍTULO DO ITEM + ÍCONE --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="m-0 fw-semibold item-title">
                            <span class="item-icon me-2">✔️</span>
                            {{ $item->produto->nome }}
                        </h6>

                        <span class="badge bg-dark small">
                            Pedida: {{ $item->quantidade }}
                        </span>
                    </div>

                    <div class="row g-2 mt-1">

                        {{-- PREÇO COMPRA --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Preço Compra</label>
                            <input type="number" step="0.01" class="form-control form-control-sm"
                                name="itens[{{ $item->id }}][preco_compra]"
                                value="{{ $item->valor_unitario }}"
                                min="0" required>
                        </div>

                        {{-- VALIDADE --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Validade do Lote</label>
                            <input type="date" class="form-control form-control-sm"
                                name="itens[{{ $item->id }}][validade_lote]"
                                value="{{ $dataPadrao }}"
                                min="{{ $dataMinima }}" required>
                        </div>

                        {{-- QTD RECEBIDA --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Qtd Recebida</label>
                            <input type="number" step="1" min="0"
                                class="form-control form-control-sm qtdRecebida"
                                data-qtd-pedida="{{ $item->quantidade }}"
                                name="itens[{{ $item->id }}][quantidade_recebida]"
                                value="{{ $item->quantidade }}" required>

                            {{-- AVISO DISCRETO --}}
                            <small class="text-warning fw-semibold aviso-qtd d-none">
                                Atenção: quantidade recebida é menor que a pedida.
                            </small>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-12 mt-1">
                            <label class="form-label small fw-bold">Status</label>
                            <select class="form-control form-control-sm selectStatus"
                                name="itens[{{ $item->id }}][criar_lote]" required>
                                <option value="1">Produto ENTREGUE (Criar lote)</option>
                                <option value="0">Produto NÃO ENTREGUE (Não criar lote)</option>
                            </select>
                        </div>

                        <input type="hidden" name="itens[{{ $item->id }}][item_id]" value="{{ $item->id }}">
                    </div>
                </div>

            @endforeach
        </div>

        <button class="btn btn-success mt-3 px-4">Confirmar Recebimento</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3 px-4">Voltar</a>

    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // =======================
    // ESTILOS DO STATUS
    // =======================
    const selects = document.querySelectorAll(".selectStatus");

    selects.forEach(select => {
        const card = select.closest(".item-card");
        const title = card.querySelector(".item-title");
        const icon = card.querySelector(".item-icon");

        aplicarEstilo(card, select.value, title, icon);

        select.addEventListener("change", function () {
            aplicarEstilo(card, this.value, title, icon);
        });
    });

    function aplicarEstilo(card, valor, title, icon) {
        card.classList.remove("border-danger","border-success");
        icon.textContent = "";
        title.classList.remove("text-success","text-danger");

        if (valor === "0") {
            card.classList.add("border-danger");
            title.classList.add("text-danger");
            icon.textContent = "❌";
        } else {
            card.classList.add("border-success");
            title.classList.add("text-success");
            icon.textContent = "✔️";
        }
    }

    // =======================
    // AVISO QUANDO RECEBIDO < PEDIDO
    // =======================
    const inputsQtd = document.querySelectorAll(".qtdRecebida");

    inputsQtd.forEach(input => {
        input.addEventListener("input", function () {
            const pedida = parseFloat(this.dataset.qtdPedida);
            const recebida = parseFloat(this.value);
            const aviso = this.parentElement.querySelector(".aviso-qtd");

            if (recebida < pedida) {
                aviso.classList.remove("d-none");
            } else {
                aviso.classList.add("d-none");
            }
        });
    });

});
</script>
@endsection
