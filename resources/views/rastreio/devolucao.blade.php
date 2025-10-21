@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Devolução / Troca</h2>

    <form action="{{ route('rastreio.devolucao.confirmar') }}" method="POST">
        @csrf

        <input type="hidden" name="venda_id" value="{{ $venda->id }}">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade Comprada</th>
                    <th>Quantidade Já Devolvida</th>
                    <th>Quantidade a Devolver</th>
                    <th>Devolver Item Completo</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venda->itens as $item)
                @php
                    $qtdeMax = $item->quantidade - $item->quantidade_devolvida;
                @endphp
                <tr>
                    <td>{{ $item->produto->nome }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->quantidade_devolvida }}</td>
                    <td>
                        <input type="number" name="itens[{{ $item->id }}][quantidade]" 
                               class="form-control" min="0" max="{{ $qtdeMax }}" 
                               value="0">
                    </td>
                    <td>
                        <input type="checkbox" name="itens[{{ $item->id }}][completo]" 
                               class="form-check-input">
                    </td>
                    <td>
                        <input type="text" name="itens[{{ $item->id }}][motivo]" 
                               class="form-control" placeholder="Motivo da devolução">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                Confirmar Devolução
            </button>
        </div>
    </form>
</div>

<script>
    // Se marcar "Devolver Item Completo", preenche automaticamente o input de quantidade
    document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const inputQuantidade = row.querySelector('input[type="number"]');
            const qtdeMax = parseInt(inputQuantidade.max);

            if(this.checked) {
                inputQuantidade.value = qtdeMax;
                inputQuantidade.disabled = true;
            } else {
                inputQuantidade.value = 0;
                inputQuantidade.disabled = false;
            }
        });
    });
</script>
@endsection
