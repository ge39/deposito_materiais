@extends('layouts.app')

@section('title', 'Lançamento Manual de Valores - Caixa #'.$caixa->id)

@section('content')
<!-- Modal de Confirmação Visual -->
<div class="modal fade" id="modalConfirmarFechamento" tabindex="-1" aria-labelledby="modalConfirmarFechamentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-warning shadow-lg">
      
      <div class="modal-header bg-warning text-dark d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-2"></i>
        <h5 class="modal-title fw-bold" id="modalConfirmarFechamentoLabel">Atenção: Fechamento de Caixa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      
      <div class="modal-body fs-6">
        <p>
          Você está prestes a fechar o caixa. <strong>Valores incorretos ou duvidosos</strong> lançados podem acarretar o bloqueio do caixa e <strong>passível de Auditoria</strong>.
        </p>
        <p class="text-danger fw-bold mb-0">
          Confirme apenas se os valores estiverem corretos.
        </p>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Não, cancelar</button>
        <button type="button" class="btn btn-warning btn-sm fw-bold" id="confirmarFechamento">
          Sim, fechar caixa
        </button>
      </div>
      
    </div>
  </div>
</div>

<div class="container">
    <h2 class="mb-4">Fechamento de Caixa  - Caixa #{{ $caixa->id }}</h2>
   <div class="card mb-4 shadow-sm">
    <div class="card-header fs-5 bg-primary text-white fw-bold">
        Informações do Caixa
    </div>
    <div class="card-body" style="font-size:14px;">
        <div class="row mb-3">
            <div class="col-md-2 fw-semibold text-muted">Operador</div>
            <div class="col-md-3">{{ $caixa->usuario->name }}</div>

            <div class="col-md-2 fw-semibold text-muted">Terminal</div>
            <div class="col-md-3">{{ $caixa->terminal_id ?? 'N/A' }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2 fw-semibold text-muted">Data Abertura</div>
            <div class="col-md-3">{{ $caixa->data_abertura->format('d/m/Y H:i') }}</div>

            <div class="col-md-2 fw-semibold text-muted">Status</div>
            <div class="col-md-3">
                <span class="badge {{ $caixa->status === 'aberto' ? 'bg-success' : ($caixa->status === 'fechado' ? 'bg-secondary' : 'bg-warning') }}">
                    {{ ucfirst($caixa->status) }}
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 fw-semibold text-muted">Fundo de Troco</div>
            <div class="col-md-3">R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}</div>
        </div>
    </div>
</div>


    
    <form action="{{ route('fechamento.fechar', $caixa->id) }}" method="POST">
    @csrf

    <!-- =============================== -->
    <!-- PAGAMENTOS + BANDEIRAS -->
    <!-- =============================== -->
    <div class="row mt-4">
        <!-- Valores por Forma de Pagamento -->
        <div class="col-md-6">
            <div class="card-header bg-primary fs-5 text-white fw-bold p-2">Valores por Forma de Pagamento</div>
            <div class="card mb-3">
                <div class="card-body">

                    @foreach ([
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'Pix',
                        'carteira' => 'Carteira',
                        'cartao_debito' => 'Cartão Débito',
                        'cartao_credito' => 'Cartão Crédito'
                    ] as $name => $label)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-semibold" style="font-size:14px;">
                                {{ $label }}
                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="{{ $name }}"
                                    class="form-control form-control-sm"
                                    style="font-size:14px;"
                                    value="{{ old($name, 0) }}"
                                    required>
                            </div>
                        </div>
                        
                    @endforeach

                </div>
            </div>
        </div>

        <!-- Bandeiras de Cartão -->
        <div class="col-md-6">
            <div class="card-header fs-5 bg-primary text-white fw-bold p-2">Bandeiras de Cartão</div>
            <div class="card mb-3">
                <div class="card-body">

                    @foreach ([
                        'bandeira_visa' => 'Visa',
                        'bandeira_mastercard' => 'Mastercard',
                        'bandeira_elo' => 'Elo',
                        'bandeira_amex' => 'Amex',
                        'bandeira_hipercard' => 'Hipercard'                        
                    ] as $name => $label)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-semibold" style="font-size:14px;">
                                {{ $label }}
                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="{{ $name }}"
                                    class="form-control form-control-sm"
                                    style="font-size:14px;"
                                    value="{{ old($name, 0) }}">
                            </div>
                        </div>
                       
                    @endforeach
                        
                </div>
            </div>
        </div>

    </div>

    <!-- =============================== -->
    <!-- ENTRADAS + SAÍDAS DE CAIXA -->
    <!-- =============================== -->
    <div class="row mt-4">

        <!-- Entradas -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fs-5 bg-primary text-white fw-bold">
                    Entradas de Caixa
                </div>
                <div class="card-body">

                    @foreach ([
                        'entrada_suprimento' => 'Suprimento',
                        'entrada_ajuste' => 'Ajuste Positivo',
                        'entrada_devolucao' => 'Devolução em Dinheiro',
                        'entrada_outros' => 'Outras Entradas'
                    ] as $name => $label)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-bold" style="font-size:14px;">
                                {{ $label }}
                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="{{ $name }}"
                                    class="form-control form-control-sm text-end"
                                    value="{{ old($name, 0) }}">
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

        <!-- Saídas -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fs-5 bg-primary text-white fw-bold">
                    Saídas de Caixa
                </div>
                <div class="card-body">

                    @foreach ([
                        'saida_sangria' => 'Sangria',
                        'saida_despesa' => 'Despesas',
                        'saida_ajuste' => 'Ajuste Negativo',
                        'saida_outros' => 'Outras Saídas'
                    ] as $name => $label)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-4 fw-bold" style="font-size:14px;">
                                {{ $label }}
                            </div>
                            <div class="col-md-8">
                                <input type="number" step="0.01"
                                    name="{{ $name }}"
                                    class="form-control form-control-sm text-end"
                                    value="{{ old($name, 0) }}">
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>

    <!-- =============================== -->
    <!-- BOTÕES -->
    <!-- =============================== -->
    <div class="row mt-4">
        <div class="col-md-12 text-end">
            <button type="button" id="btnFecharCaixa" class="btn btn-success">
                    Fechar Caixa
                </button>
            <a href="{{ route('fechamento.lista') }}" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </div>
    
  

</form>

@endsection

  <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('fechamento.fechar', $caixa->id) }}"]');
    const btnFechar = document.getElementById('btnFecharCaixa'); // botão original
    const btnConfirmar = document.getElementById('confirmarFechamento');
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarFechamento'));

    btnFechar.addEventListener('click', function(e) {
        e.preventDefault(); // impede envio direto
        modal.show();
    });

    btnConfirmar.addEventListener('click', function() {
        form.submit(); // envia o form apenas se confirmar
    });
});
</script>


