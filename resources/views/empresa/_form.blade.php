@php
    $editando = isset($empresa);

    $valor = static function (string $campo, $padrao = null) use ($editando, $empresa ?? null) {
        return old(
            $campo,
            $editando
                ? data_get($empresa, $campo, $padrao)
                : $padrao
        );
    };
@endphp

@push('styles')
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>
    .empresa-form-card {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }

    .empresa-form-section {
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 1rem;
        background: #fff;
    }

    .empresa-form-section-title {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: 1rem;
        font-size: .95rem;
        font-weight: 700;
    }

    .empresa-localizacao-status {
        min-height: 42px;
        display: flex;
        align-items: center;
    }

    #mapaEmpresa {
        width: 100%;
        height: 340px;
        border: 1px solid #ced4da;
        border-radius: 10px;
        background: #f8f9fa;
        overflow: hidden;
    }

    .empresa-coordenada-validada {
        border-color: #198754 !important;
        background-color: #f2fff7;
    }

    .empresa-coordenada-pendente {
        border-color: #ffc107 !important;
        background-color: #fffdf2;
    }

    @media (max-width: 767.98px) {
        #mapaEmpresa {
            height: 280px;
        }
    }
</style>
@endpush

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-2">
            Verifique os dados informados:
        </div>

        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm empresa-form-card">
    <div class="card-body p-3 p-lg-4">

        {{-- IDENTIFICAÇÃO --}}
        <section class="empresa-form-section mb-3">
            <div class="empresa-form-section-title">
                <i class="bi bi-building"></i>
                Identificação
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">
                        Nome <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="nome"
                        id="nome"
                        class="form-control"
                        value="{{ $valor('nome') }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label for="cnpj" class="form-label">CNPJ</label>

                    <input
                        type="text"
                        name="cnpj"
                        id="cnpj"
                        class="form-control"
                        value="{{ $valor('cnpj') }}"
                        maxlength="18"
                    >
                </div>

                <div class="col-md-6">
                    <label for="inscricao_estadual" class="form-label">
                        Inscrição Estadual
                    </label>

                    <input
                        type="text"
                        name="inscricao_estadual"
                        id="inscricao_estadual"
                        class="form-control"
                        value="{{ $valor('inscricao_estadual') }}"
                        maxlength="20"
                    >
                </div>

                <div class="col-md-6">
                    <label for="telefone" class="form-label">Telefone</label>

                    <input
                        type="text"
                        name="telefone"
                        id="telefone"
                        class="form-control"
                        value="{{ $valor('telefone') }}"
                        maxlength="20"
                    >
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail</label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="{{ $valor('email') }}"
                        maxlength="100"
                    >
                </div>

                <div class="col-md-6">
                    <label for="site" class="form-label">Site</label>

                    <input
                        type="text"
                        name="site"
                        id="site"
                        class="form-control"
                        value="{{ $valor('site') }}"
                        maxlength="100"
                    >
                </div>
            </div>
        </section>

        {{-- ENDEREÇO E GEOLOCALIZAÇÃO --}}
        <section class="empresa-form-section mb-3">
            <div class="empresa-form-section-title">
                <i class="bi bi-geo-alt"></i>
                Endereço e geolocalização
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label for="cep" class="form-label">CEP</label>

                    <input
                        type="text"
                        name="cep"
                        id="cep"
                        class="form-control"
                        value="{{ $valor('cep') }}"
                        maxlength="10"
                        autocomplete="postal-code"
                    >
                </div>

                <div class="col-md-7">
                    <label for="endereco" class="form-label">Endereço</label>

                    <input
                        type="text"
                        name="endereco"
                        id="endereco"
                        class="form-control"
                        value="{{ $valor('endereco') }}"
                        maxlength="255"
                        autocomplete="street-address"
                    >
                </div>

                <div class="col-md-2">
                    <label for="numero" class="form-label">Número</label>

                    <input
                        type="text"
                        name="numero"
                        id="numero"
                        class="form-control"
                        value="{{ $valor('numero') }}"
                        maxlength="10"
                    >
                </div>

                <div class="col-md-4">
                    <label for="complemento" class="form-label">
                        Complemento
                    </label>

                    <input
                        type="text"
                        name="complemento"
                        id="complemento"
                        class="form-control"
                        value="{{ $valor('complemento') }}"
                        maxlength="50"
                    >
                </div>

                <div class="col-md-3">
                    <label for="bairro" class="form-label">Bairro</label>

                    <input
                        type="text"
                        name="bairro"
                        id="bairro"
                        class="form-control"
                        value="{{ $valor('bairro') }}"
                        maxlength="50"
                    >
                </div>

                <div class="col-md-3">
                    <label for="cidade" class="form-label">Cidade</label>

                    <input
                        type="text"
                        name="cidade"
                        id="cidade"
                        class="form-control"
                        value="{{ $valor('cidade') }}"
                        maxlength="50"
                    >
                </div>

                <div class="col-md-2">
                    <label for="estado" class="form-label">UF</label>

                    <input
                        type="text"
                        name="estado"
                        id="estado"
                        class="form-control text-uppercase"
                        value="{{ $valor('estado') }}"
                        maxlength="2"
                        autocomplete="address-level1"
                    >
                </div>

                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button
                            type="button"
                            id="validarEndereco"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            Validar endereço
                        </button>

                        <span class="text-muted small">
                            O CEP preenche o endereço e, em seguida, gera latitude e longitude.
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="latitude" class="form-label">Latitude</label>

                    <input
                        type="number"
                        name="latitude"
                        id="latitude"
                        class="form-control empresa-coordenada-pendente"
                        value="{{ $valor('latitude') }}"
                        min="-90"
                        max="90"
                        step="0.0000001"
                        readonly
                    >
                </div>

                <div class="col-md-6">
                    <label for="longitude" class="form-label">Longitude</label>

                    <input
                        type="number"
                        name="longitude"
                        id="longitude"
                        class="form-control empresa-coordenada-pendente"
                        value="{{ $valor('longitude') }}"
                        min="-180"
                        max="180"
                        step="0.0000001"
                        readonly
                    >
                </div>

                <div class="col-12">
                    <div
                        id="localizacaoStatus"
                        class="empresa-localizacao-status alert alert-secondary mb-0"
                    >
                        Informe o CEP ou use o botão Validar endereço.
                    </div>
                </div>

                <div class="col-12">
                    <div id="mapaEmpresa"></div>

                    <small class="text-muted d-block mt-2">
                        Após a localização ser gerada, arraste o marcador para corrigir o ponto.
                    </small>
                </div>
            </div>
        </section>

        {{-- SITUAÇÃO --}}
        <section class="empresa-form-section">
            <div class="empresa-form-section-title">
                <i class="bi bi-toggle-on"></i>
                Situação
            </div>

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="ativo"
                    id="ativo"
                    class="form-check-input"
                    value="1"
                    {{ old('ativo', $editando ? (bool) $empresa->ativo : true) ? 'checked' : '' }}
                >

                <label for="ativo" class="form-check-label">
                    Empresa ativa
                </label>
            </div>
        </section>

        <div class="mt-4 d-flex justify-content-end gap-2 flex-wrap">
            <a
                href="{{ route('empresa.index') }}"
                class="btn btn-outline-secondary"
            >
                Cancelar
            </a>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i>
                {{ $editando ? 'Atualizar' : 'Salvar' }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campos = {
        cep: document.getElementById('cep'),
        endereco: document.getElementById('endereco'),
        numero: document.getElementById('numero'),
        bairro: document.getElementById('bairro'),
        cidade: document.getElementById('cidade'),
        estado: document.getElementById('estado'),
        latitude: document.getElementById('latitude'),
        longitude: document.getElementById('longitude'),
    };

    const validarButton =
        document.getElementById('validarEndereco');

    const statusBox =
        document.getElementById('localizacaoStatus');

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

    let marcador = null;
    let consultaEmAndamento = false;

    const latitudeInicial =
        parseFloat(campos.latitude.value);

    const longitudeInicial =
        parseFloat(campos.longitude.value);

    const possuiCoordenadas =
        Number.isFinite(latitudeInicial)
        && Number.isFinite(longitudeInicial);

    const mapa = L.map('mapaEmpresa').setView(
        possuiCoordenadas
            ? [latitudeInicial, longitudeInicial]
            : [-23.5505200, -46.6333080],
        possuiCoordenadas ? 18 : 10
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution:
                '&copy; OpenStreetMap contributors',
        }
    ).addTo(mapa);

    function atualizarStatus(tipo, mensagem) {
        const classes = {
            neutro: 'alert-secondary',
            carregando: 'alert-info',
            sucesso: 'alert-success',
            erro: 'alert-danger',
        };

        statusBox.className =
            'empresa-localizacao-status alert mb-0 '
            + (classes[tipo] || classes.neutro);

        statusBox.textContent = mensagem;
    }

    function marcarCoordenadasComoValidas(validas) {
        [campos.latitude, campos.longitude].forEach(
            function (campo) {
                campo.classList.toggle(
                    'empresa-coordenada-validada',
                    validas
                );

                campo.classList.toggle(
                    'empresa-coordenada-pendente',
                    ! validas
                );
            }
        );
    }

    function limparCoordenadas() {
        campos.latitude.value = '';
        campos.longitude.value = '';
        marcarCoordenadasComoValidas(false);

        if (marcador) {
            mapa.removeLayer(marcador);
            marcador = null;
        }
    }

    function atualizarCoordenadas(
        latitude,
        longitude,
        centralizar = true
    ) {
        const lat = Number(latitude);
        const lng = Number(longitude);

        if (
            ! Number.isFinite(lat)
            || ! Number.isFinite(lng)
        ) {
            limparCoordenadas();
            return;
        }

        campos.latitude.value = lat.toFixed(7);
        campos.longitude.value = lng.toFixed(7);

        marcarCoordenadasComoValidas(true);

        const posicao = [lat, lng];

        if (! marcador) {
            marcador = L.marker(
                posicao,
                {
                    draggable: true,
                }
            ).addTo(mapa);

            marcador.on('dragend', function () {
                const ponto = marcador.getLatLng();

                campos.latitude.value =
                    Number(ponto.lat).toFixed(7);

                campos.longitude.value =
                    Number(ponto.lng).toFixed(7);

                marcarCoordenadasComoValidas(true);

                atualizarStatus(
                    'sucesso',
                    'Coordenadas ajustadas manualmente no mapa.'
                );
            });
        } else {
            marcador.setLatLng(posicao);
        }

        if (centralizar) {
            mapa.setView(posicao, 18);
        }
    }

    function enderecoMinimoValido() {
        return campos.endereco.value.trim() !== ''
            && campos.cidade.value.trim() !== ''
            && campos.estado.value.trim().length === 2;
    }

    async function geocodificarEndereco() {
        if (consultaEmAndamento) {
            return;
        }

        if (! enderecoMinimoValido()) {
            atualizarStatus(
                'erro',
                'Informe endereço, cidade e UF antes de gerar as coordenadas.'
            );

            return;
        }

        consultaEmAndamento = true;
        validarButton.disabled = true;

        atualizarStatus(
            'carregando',
            'Gerando latitude e longitude...'
        );

        try {
            const resposta = await fetch(
                @json(route('empresa.geocodificar')),
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        cep: campos.cep.value,
                        endereco: campos.endereco.value,
                        numero: campos.numero.value,
                        bairro: campos.bairro.value,
                        cidade: campos.cidade.value,
                        estado: campos.estado.value,
                    }),
                }
            );

            const dados = await resposta.json();

            if (! resposta.ok || ! dados.success) {
                throw new Error(
                    dados.message
                    || 'Não foi possível gerar as coordenadas.'
                );
            }

            atualizarCoordenadas(
                dados.latitude,
                dados.longitude
            );

            atualizarStatus(
                'sucesso',
                'Localização validada: '
                + (
                    dados.endereco_formatado
                    || 'coordenadas geradas com sucesso.'
                )
            );
        } catch (erro) {
            limparCoordenadas();

            atualizarStatus(
                'erro',
                erro.message
                || 'Falha ao gerar latitude e longitude.'
            );
        } finally {
            consultaEmAndamento = false;
            validarButton.disabled = false;
        }
    }

    async function consultarCep() {
        const cep =
            campos.cep.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            limparCoordenadas();

            atualizarStatus(
                'erro',
                'Informe um CEP com 8 dígitos.'
            );

            return;
        }

        atualizarStatus(
            'carregando',
            'Consultando CEP...'
        );

        try {
            const resposta = await fetch(
                `/buscar-cep?cep=${cep}`,
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                }
            );

            const dados = await resposta.json();

            if (! resposta.ok || dados.erro) {
                throw new Error('CEP não localizado.');
            }

            campos.endereco.value =
                dados.logradouro || '';

            campos.bairro.value =
                dados.bairro || '';

            campos.cidade.value =
                dados.localidade || '';

            campos.estado.value =
                dados.uf || '';

            limparCoordenadas();

            atualizarStatus(
                'carregando',
                'CEP localizado. Gerando latitude e longitude...'
            );

            await geocodificarEndereco();
        } catch (erro) {
            limparCoordenadas();

            atualizarStatus(
                'erro',
                erro.message
                || 'Não foi possível consultar o CEP.'
            );
        }
    }

    campos.cep.addEventListener(
        'blur',
        consultarCep
    );

    validarButton.addEventListener(
        'click',
        geocodificarEndereco
    );

    campos.numero.addEventListener(
        'blur',
        function () {
            if (enderecoMinimoValido()) {
                geocodificarEndereco();
            }
        }
    );

    [
        campos.endereco,
        campos.bairro,
        campos.cidade,
        campos.estado,
    ].forEach(function (campo) {
        campo.addEventListener(
            'input',
            function () {
                limparCoordenadas();

                atualizarStatus(
                    'neutro',
                    'Endereço alterado. Valide novamente para atualizar as coordenadas.'
                );
            }
        );
    });

    if (possuiCoordenadas) {
        atualizarCoordenadas(
            latitudeInicial,
            longitudeInicial,
            false
        );

        atualizarStatus(
            'sucesso',
            'Localização cadastrada. O marcador pode ser ajustado manualmente.'
        );
    }

    setTimeout(function () {
        mapa.invalidateSize();
    }, 250);
});
</script>

<script src="{{ asset('js/form-masks.js') }}"></script>
@endpush