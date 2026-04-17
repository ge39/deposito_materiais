<h2>Histórico do Orçamento #{{ $orcamentoId }}</h2>

@foreach($movimentacoes as $mov)
    <div style="margin-bottom:10px; padding:10px; border:1px solid #ccc;">
        <strong>{{ strtoupper($mov->tipo) }}</strong><br>

        {{ $mov->descricao }}<br>

        @if($mov->quantidade)
            Quantidade: {{ $mov->quantidade }}<br>
        @endif

        <small>
            {{ $mov->created_at->format('d/m/Y H:i') }}
        </small>
    </div>
@endforeach