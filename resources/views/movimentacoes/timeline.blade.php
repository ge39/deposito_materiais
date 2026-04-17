<h3>Histórico do Orçamento</h3>

<div class="timeline">
    @foreach($movimentacoes as $mov)
        <div class="timeline-item">
            
            <div class="timeline-icon 
                @if($mov->tipo == 'atendido') bg-success
                @elseif($mov->tipo == 'pendente') bg-warning
                @elseif($mov->tipo == 'reserva') bg-primary
                @else bg-secondary
                @endif">
            </div>

            <div class="timeline-content">
                <strong>
                    @switch($mov->tipo)
                        @case('atendido') ✔ Atendido @break
                        @case('pendente') ⚠ Pendente @break
                        @case('reserva') 📦 Reserva @break
                        @case('status') 🔄 Status @break
                        @default ℹ Informação
                    @endswitch
                </strong>

                <p>{{ $mov->descricao }}</p>

                @if($mov->quantidade)
                    <small>Quantidade: {{ $mov->quantidade }}</small><br>
                @endif

                <small class="text-muted">
                    {{ $mov->created_at->format('d/m/Y H:i') }}
                </small>
            </div>

        </div>
    @endforeach
</div>