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
                ];

                $possuiRastreador = (bool) old(
                    'possui_rastreador',
                    $veiculo->possui_rastreador ?? false
                );

                $rastreamentoAtivo = (bool) old(
                    'rastreamento_ativo',
                    $veiculo->exists
                        ? ($veiculo->rastreamento_ativo ?? false)
                        : true
                );
            @endphp

            @foreach($recursos as $campo => $dados)
                <div class="col-md-4">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="form-check form-switch">
                            <input
                                type="hidden"
                                name="{{ $campo }}"
                                value="0"
                            >

                            <input
                                class="form-check-input @error($campo) is-invalid @enderror"
                                type="checkbox"
                                role="switch"
                                name="{{ $campo }}"
                                id="{{ $campo }}"
                                value="1"
                                {{ old($campo, $veiculo->$campo ?? false) ? 'checked' : '' }}
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="{{ $campo }}"
                            >
                                {{ $dados['label'] }}
                            </label>

                            @error($campo)
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
            Rastreamento
        </h6>

        <small class="text-muted">
            Vincule o veículo ao identificador exibido no cadastro do dispositivo no Traccar.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3 align-items-stretch">

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="form-check form-switch">
                        <input
                            type="hidden"
                            name="possui_rastreador"
                            value="0"
                        >

                        <input
                            class="form-check-input @error('possui_rastreador') is-invalid @enderror"
                            type="checkbox"
                            role="switch"
                            name="possui_rastreador"
                            id="possui_rastreador"
                            value="1"
                            {{ $possuiRastreador ? 'checked' : '' }}
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="possui_rastreador"
                        >
                            Possui rastreador
                        </label>

                        @error('possui_rastreador')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <small class="text-muted d-block mt-2">
                        Indica que este veículo possui um dispositivo de rastreamento vinculado.
                    </small>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <label
                        for="traccar_unique_id"
                        class="form-label fw-semibold"
                    >
                        Identificador do dispositivo Traccar
                    </label>

                    <input
                        type="text"
                        maxlength="128"
                        class="form-control @error('traccar_unique_id') is-invalid @enderror"
                        id="traccar_unique_id"
                        name="traccar_unique_id"
                        value="{{ old('traccar_unique_id', $veiculo->traccar_unique_id ?? '') }}"
                        placeholder="Ex.: 87431092"
                        autocomplete="off"
                        {{ $possuiRastreador ? '' : 'disabled' }}
                    >

                    @error('traccar_unique_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <small class="text-muted d-block mt-2">
                        Informe o identificador visível no Traccar
                        (<strong>uniqueId</strong>).
                    </small>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                    <div class="form-check form-switch">
                        <input
                            type="hidden"
                            name="rastreamento_ativo"
                            value="0"
                        >

                        <input
                            class="form-check-input @error('rastreamento_ativo') is-invalid @enderror"
                            type="checkbox"
                            role="switch"
                            name="rastreamento_ativo"
                            id="rastreamento_ativo"
                            value="1"
                            {{ $rastreamentoAtivo ? 'checked' : '' }}
                            {{ $possuiRastreador ? '' : 'disabled' }}
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="rastreamento_ativo"
                        >
                            Rastreamento ativo
                        </label>

                        @error('rastreamento_ativo')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <small class="text-muted d-block mt-2">
                        Desative para manter o vínculo com o Traccar sem exibir o veículo no rastreamento operacional.
                    </small>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const possuiRastreador =
                document.getElementById(
                    'possui_rastreador'
                );

            const traccarUniqueId =
                document.getElementById(
                    'traccar_unique_id'
                );

            const rastreamentoAtivo =
                document.getElementById(
                    'rastreamento_ativo'
                );

            if (
                ! possuiRastreador
                || ! traccarUniqueId
                || ! rastreamentoAtivo
            ) {
                return;
            }

            function atualizarEstadoRastreamento() {
                const habilitado =
                    possuiRastreador.checked;

                traccarUniqueId.disabled =
                    ! habilitado;

                rastreamentoAtivo.disabled =
                    ! habilitado;

                if (! habilitado) {
                    rastreamentoAtivo.checked =
                        false;
                }
            }

            possuiRastreador.addEventListener(
                'change',
                atualizarEstadoRastreamento
            );

            atualizarEstadoRastreamento();
        }
    );
</script>
