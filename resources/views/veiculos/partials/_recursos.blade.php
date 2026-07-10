<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-tools me-2 text-warning"></i>
            Recursos do Veículo
        </h6>
        <small class="text-muted">
            Recursos físicos e operacionais disponíveis para carregamento, transporte e entrega.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            @php
                $recursos = [
                    'possui_munck' => [
                        'label' => 'Possui munck',
                        'desc' => 'Indicado para cargas pesadas ou descarga mecanizada.'
                    ],
                    'possui_carroceria_aberta' => [
                        'label' => 'Carroceria aberta',
                        'desc' => 'Útil para areia, pedra, blocos e materiais grandes.'
                    ],
                    'possui_carroceria_fechada' => [
                        'label' => 'Carroceria fechada',
                        'desc' => 'Melhor para produtos sensíveis ou protegidos.'
                    ],
                    'possui_rastreador' => [
                        'label' => 'Possui rastreador',
                        'desc' => 'Permite controle operacional e acompanhamento futuro.'
                    ],
                ];
            @endphp

            @foreach($recursos as $campo => $dados)
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="form-check form-switch">
                            <input type="hidden" name="{{ $campo }}" value="0">

                            <input
                                class="form-check-input @error($campo) is-invalid @enderror"
                                type="checkbox"
                                role="switch"
                                name="{{ $campo }}"
                                id="{{ $campo }}"
                                value="1"
                                {{ old($campo, $veiculo->$campo ?? false) ? 'checked' : '' }}
                            >

                            <label class="form-check-label fw-semibold" for="{{ $campo }}">
                                {{ $dados['label'] }}
                            </label>

                            @error($campo)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <small class="text-muted d-block mt-2">
                            {{ $dados['desc'] }}
                        </small>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>