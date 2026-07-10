{{-- ============================================================
     IDENTIFICAÇÃO DO VEÍCULO

     Responsabilidade:
     - Exibir os campos da etapa de identificação.
     - Preservar validações e valores antigos.
     - Controlar Tipo → Classe → Carroceria.
     - Não contém <form>, @csrf, wizard ou rodapé.
============================================================ --}}

<x-erp.cadastro.section
    title="Identificação do Veículo"
    description="Informações principais para identificar, classificar e disponibilizar o veículo para a operação."
    icon="bi bi-truck-front"
>
    {{-- ========================================================
         DADOS BÁSICOS
    ========================================================= --}}
    <div class="card border rounded-3 shadow-none mb-4">

        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between gap-3">

                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-primary">
                        <i class="bi bi-card-checklist"></i>
                    </span>

                    <div>
                        <h3 class="h6 fw-bold text-dark mb-0">
                            Dados Básicos
                        </h3>

                        <small class="text-muted">
                            Dados principais de identificação e vínculo do veículo.
                        </small>
                    </div>
                </div>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    Campos com * são obrigatórios
                </span>

            </div>
        </div>

        <div class="card-body p-4">

            <div class="row g-4">

                {{-- Placa --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label
                        for="placa"
                        class="form-label fw-semibold"
                    >
                        Placa
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-car-front"></i>
                        </span>

                        <input
                            type="text"
                            name="placa"
                            id="placa"
                            class="form-control @error('placa') is-invalid @enderror"
                            value="{{ old('placa', $veiculo->placa ?? '') }}"
                            placeholder="Ex.: ABC1D23"
                            maxlength="20"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                            required
                        >

                        @error('placa')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Marca --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label
                        for="marca"
                        class="form-label fw-semibold"
                    >
                        Fabricante / Marca
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-shield-check"></i>
                        </span>

                        <input
                            type="text"
                            name="marca"
                            id="marca"
                            class="form-control @error('marca') is-invalid @enderror"
                            value="{{ old('marca', $veiculo->marca ?? '') }}"
                            placeholder="Ex.: Mercedes-Benz"
                            maxlength="80"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                        >

                        @error('marca')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Modelo --}}
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label
                        for="modelo"
                        class="form-label fw-semibold"
                    >
                        Modelo
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-truck"></i>
                        </span>

                        <input
                            type="text"
                            name="modelo"
                            id="modelo"
                            class="form-control @error('modelo') is-invalid @enderror"
                            value="{{ old('modelo', $veiculo->modelo ?? '') }}"
                            placeholder="Ex.: Accelo 815"
                            maxlength="100"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                            required
                        >

                        @error('modelo')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Ano de fabricação --}}
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="ano_fabricacao"
                        class="form-label fw-semibold"
                    >
                        Ano de fabricação
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-calendar3"></i>
                        </span>

                        <input
                            type="number"
                            name="ano_fabricacao"
                            id="ano_fabricacao"
                            class="form-control @error('ano_fabricacao') is-invalid @enderror"
                            value="{{ old('ano_fabricacao', $veiculo->ano_fabricacao ?? '') }}"
                            placeholder="Ex.: 2024"
                            min="1900"
                            max="{{ now()->year + 1 }}"
                        >

                        @error('ano_fabricacao')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Cor --}}
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="cor"
                        class="form-label fw-semibold"
                    >
                        Cor
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-palette"></i>
                        </span>

                        <input
                            type="text"
                            name="cor"
                            id="cor"
                            class="form-control @error('cor') is-invalid @enderror"
                            value="{{ old('cor', $veiculo->cor ?? '') }}"
                            placeholder="Ex.: Branco"
                            maxlength="50"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                        >

                        @error('cor')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Tipo da frota --}}
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="tipo_frota"
                        class="form-label fw-semibold"
                    >
                        Tipo da Frota
                    </label>

                    @php
                        $tipoFrotaSelecionado = old(
                            'tipo_frota',
                            $veiculo->tipo_frota ?? 'Frota'
                        );
                    @endphp

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-buildings"></i>
                        </span>

                        <select
                            name="tipo_frota"
                            id="tipo_frota"
                            class="form-select @error('tipo_frota') is-invalid @enderror"
                        >
                            <option
                                value="Frota"
                                @selected($tipoFrotaSelecionado === 'Frota')
                            >
                                Frota própria
                            </option>

                            <option
                                value="Agregado"
                                @selected($tipoFrotaSelecionado === 'Agregado')
                            >
                                Agregado
                            </option>

                            <option
                                value="Terceirizado"
                                @selected($tipoFrotaSelecionado === 'Terceirizado')
                            >
                                Terceirizado
                            </option>
                        </select>

                        @error('tipo_frota')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Motorista padrão --}}
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="motorista_padrao_id"
                        class="form-label fw-semibold"
                    >
                        Motorista padrão
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-person"></i>
                        </span>

                        <select
                            name="motorista_padrao_id"
                            id="motorista_padrao_id"
                            class="form-select @error('motorista_padrao_id') is-invalid @enderror"
                        >
                            <option value="">
                                Sem motorista padrão
                            </option>

                            @isset($motoristas)
                                @foreach ($motoristas as $motorista)
                                    <option
                                        value="{{ $motorista->id }}"
                                        @selected(
                                            (string) old(
                                                'motorista_padrao_id',
                                                $veiculo->motorista_padrao_id ?? ''
                                            ) === (string) $motorista->id
                                        )
                                    >
                                        {{ $motorista->nome }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>

                        @error('motorista_padrao_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ========================================================
         CLASSIFICAÇÃO
    ========================================================= --}}
    <div class="card border rounded-3 shadow-none mb-4">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex align-items-center gap-2">

                <span class="d-inline-flex align-items-center justify-content-center text-primary">
                    <i class="bi bi-diagram-3"></i>
                </span>

                <div>
                    <h3 class="h6 fw-bold text-dark mb-0">
                        Classificação
                    </h3>

                    <small class="text-muted">
                        Classificação operacional utilizada pela logística e pela expedição.
                    </small>
                </div>

            </div>

        </div>

        <div class="card-body p-4">

            <div class="row g-4">

                {{-- Tipo de veículo --}}
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="tipo_veiculo_id"
                        class="form-label fw-semibold"
                    >
                        Tipo de Veículo
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-truck-front"></i>
                        </span>

                        <select
                            name="tipo_veiculo_id"
                            id="tipo_veiculo_id"
                            class="form-select @error('tipo_veiculo_id') is-invalid @enderror"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            @foreach ($tiposVeiculo as $tipo)
                                <option
                                    value="{{ $tipo->id }}"
                                    @selected(
                                        (string) old(
                                            'tipo_veiculo_id',
                                            $veiculo->tipo_veiculo_id ?? ''
                                        ) === (string) $tipo->id
                                    )
                                >
                                    {{ $tipo->descricao }}
                                </option>
                            @endforeach
                        </select>

                        @error('tipo_veiculo_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Classe --}}
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="classe_veiculo_id"
                        class="form-label fw-semibold"
                    >
                        Classe
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-ui-checks-grid"></i>
                        </span>

                        <select
                            name="classe_veiculo_id"
                            id="classe_veiculo_id"
                            class="form-select @error('classe_veiculo_id') is-invalid @enderror"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            @isset($classesVeiculo)
                                @foreach ($classesVeiculo as $classe)
                                    <option
                                        value="{{ $classe->id }}"
                                        @selected(
                                            (string) old(
                                                'classe_veiculo_id',
                                                $veiculo->classe_veiculo_id ?? ''
                                            ) === (string) $classe->id
                                        )
                                    >
                                        {{ $classe->descricao }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>

                        @error('classe_veiculo_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Tipo de carroceria --}}
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="tipo_carroceria_id"
                        class="form-label fw-semibold"
                    >
                        Tipo de Carroceria
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-box-seam"></i>
                        </span>

                        <select
                            name="tipo_carroceria_id"
                            id="tipo_carroceria_id"
                            class="form-select @error('tipo_carroceria_id') is-invalid @enderror"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            @isset($tiposCarroceria)
                                @foreach ($tiposCarroceria as $carroceria)
                                    <option
                                        value="{{ $carroceria->id }}"
                                        @selected(
                                            (string) old(
                                                'tipo_carroceria_id',
                                                $veiculo->tipo_carroceria_id ?? ''
                                            ) === (string) $carroceria->id
                                        )
                                    >
                                        {{ $carroceria->descricao }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>

                        @error('tipo_carroceria_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- CNH exigida --}}
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="categoria_cnh"
                        class="form-label fw-semibold"
                    >
                        CNH exigida
                    </label>

                    @php
                        $categoriaSelecionada = old(
                            'categoria_cnh',
                            $veiculo->categoria_cnh ?? ''
                        );
                    @endphp

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-person-vcard"></i>
                        </span>

                        <select
                            name="categoria_cnh"
                            id="categoria_cnh"
                            class="form-select @error('categoria_cnh') is-invalid @enderror"
                        >
                            <option value="">
                                Não definida
                            </option>

                            @foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'] as $categoria)
                                <option
                                    value="{{ $categoria }}"
                                    @selected($categoriaSelecionada === $categoria)
                                >
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        </select>

                        @error('categoria_cnh')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ========================================================
         DISPONIBILIDADE E INFORMAÇÕES TÉCNICAS
    ========================================================= --}}
    <div class="row g-4">

        {{-- Disponibilidade --}}
        <div class="col-xl-5 col-lg-5">

            <div class="card border rounded-3 shadow-none h-100">

                <div class="card-header bg-white border-bottom py-3">

                    <div class="d-flex align-items-center gap-2">

                        <span class="d-inline-flex align-items-center justify-content-center text-primary">
                            <i class="bi bi-clock-history"></i>
                        </span>

                        <div>
                            <h3 class="h6 fw-bold text-dark mb-0">
                                Disponibilidade
                            </h3>

                            <small class="text-muted">
                                Situação operacional atual do veículo.
                            </small>
                        </div>

                    </div>

                </div>

                <div class="card-body p-4">

                    <label
                        for="disponibilidade"
                        class="form-label fw-semibold"
                    >
                        Disponibilidade
                        <span class="text-danger">*</span>
                    </label>

                    @php
                        $disponibilidadeSelecionada = old(
                            'disponibilidade',
                            $veiculo->disponibilidade ?? ''
                        );
                    @endphp

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-activity"></i>
                        </span>

                        <select
                            name="disponibilidade"
                            id="disponibilidade"
                            class="form-select @error('disponibilidade') is-invalid @enderror"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            <option
                                value="Disponível"
                                @selected($disponibilidadeSelecionada === 'Disponível')
                            >
                                Disponível
                            </option>

                            <option
                                value="Reservado"
                                @selected($disponibilidadeSelecionada === 'Reservado')
                            >
                                Reservado
                            </option>

                            <option
                                value="Carregando"
                                @selected($disponibilidadeSelecionada === 'Carregando')
                            >
                                Carregando
                            </option>

                            <option
                                value="Em rota"
                                @selected($disponibilidadeSelecionada === 'Em rota')
                            >
                                Em rota
                            </option>

                            <option
                                value="Manutencao"
                                @selected($disponibilidadeSelecionada === 'Manutencao')
                            >
                                Manutenção
                            </option>

                            <option
                                value="Indisponível"
                                @selected($disponibilidadeSelecionada === 'Indisponível')
                            >
                                Indisponível
                            </option>
                        </select>

                        @error('disponibilidade')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <small class="d-block text-muted mt-2">
                        Esse status será utilizado pela expedição e pelos romaneios.
                    </small>

                </div>

            </div>

        </div>

        {{-- Informações técnicas --}}
        <div class="col-xl-7 col-lg-7">

            <div class="card border rounded-3 shadow-none h-100">

                <div class="card-header bg-white border-bottom py-3">

                    <div class="d-flex align-items-center gap-2">

                        <span class="d-inline-flex align-items-center justify-content-center text-primary">
                            <i class="bi bi-gear"></i>
                        </span>

                        <div>
                            <h3 class="h6 fw-bold text-dark mb-0">
                                Informações Técnicas
                            </h3>

                            <small class="text-muted">
                                Identificadores oficiais e técnicos do veículo.
                            </small>
                        </div>

                    </div>

                </div>

                <div class="card-body p-4">

                    <div class="row g-4">

                        {{-- Chassi --}}
                        <div class="col-md-6">

                            <label
                                for="chassi"
                                class="form-label fw-semibold"
                            >
                                Chassi
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-upc-scan"></i>
                                </span>

                                <input
                                    type="text"
                                    name="chassi"
                                    id="chassi"
                                    class="form-control @error('chassi') is-invalid @enderror"
                                    value="{{ old('chassi', $veiculo->chassi ?? '') }}"
                                    placeholder="Número do chassi"
                                    maxlength="80"
                                    autocomplete="off"
                                >

                                @error('chassi')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                        {{-- Renavam --}}
                        <div class="col-md-6">

                            <label
                                for="renavam"
                                class="form-label fw-semibold"
                            >
                                Renavam
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-file-earmark-text"></i>
                                </span>

                                <input
                                    type="text"
                                    name="renavam"
                                    id="renavam"
                                    class="form-control @error('renavam') is-invalid @enderror"
                                    value="{{ old('renavam', $veiculo->renavam ?? '') }}"
                                    placeholder="Número do Renavam"
                                    maxlength="80"
                                    autocomplete="off"
                                >

                                @error('renavam')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-erp.cadastro.section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipo = document.getElementById('tipo_veiculo_id');
        const classe = document.getElementById('classe_veiculo_id');
        const carroceria = document.getElementById('tipo_carroceria_id');

        if (!tipo || !classe || !carroceria) {
            return;
        }

        const classeSelecionada = @json(
            old(
                'classe_veiculo_id',
                $veiculo->classe_veiculo_id ?? ''
            )
        );

        const carroceriaSelecionada = @json(
            old(
                'tipo_carroceria_id',
                $veiculo->tipo_carroceria_id ?? ''
            )
        );

        function definirCarregando(select, texto = 'Carregando...') {
            select.innerHTML = '';

            const option = document.createElement('option');
            option.value = '';
            option.textContent = texto;

            select.appendChild(option);
            select.disabled = true;
        }

        function definirPlaceholder(select, texto = 'Selecione...') {
            select.innerHTML = '';

            const option = document.createElement('option');
            option.value = '';
            option.textContent = texto;

            select.appendChild(option);
            select.disabled = false;
        }

        function preencherSelect(select, dados, selecionado = '') {
            definirPlaceholder(select);

            dados.forEach(function (item) {
                const option = document.createElement('option');

                option.value = item.id;
                option.textContent = item.descricao;
                option.selected =
                    String(item.id) === String(selecionado);

                select.appendChild(option);
            });
        }

        async function carregarClasses(tipoId, classeId = '') {
            definirCarregando(classe);
            definirPlaceholder(carroceria);

            if (!tipoId) {
                definirPlaceholder(classe);
                return;
            }

            try {
                const response = await fetch(
                    `/frota/classes/${encodeURIComponent(tipoId)}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Erro HTTP ${response.status}`
                    );
                }

                const dados = await response.json();

                preencherSelect(classe, dados, classeId);

                if (classeId) {
                    await carregarCarrocerias(
                        classeId,
                        carroceriaSelecionada
                    );
                }
            } catch (error) {
                console.error(
                    'Erro ao carregar classes do veículo:',
                    error
                );

                definirPlaceholder(
                    classe,
                    'Não foi possível carregar'
                );
            }
        }

        async function carregarCarrocerias(
            classeId,
            carroceriaId = ''
        ) {
            definirCarregando(carroceria);

            if (!classeId) {
                definirPlaceholder(carroceria);
                return;
            }

            try {
                const response = await fetch(
                    `/frota/carrocerias/${encodeURIComponent(classeId)}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Erro HTTP ${response.status}`
                    );
                }

                const dados = await response.json();

                preencherSelect(
                    carroceria,
                    dados,
                    carroceriaId
                );
            } catch (error) {
                console.error(
                    'Erro ao carregar carrocerias do veículo:',
                    error
                );

                definirPlaceholder(
                    carroceria,
                    'Não foi possível carregar'
                );
            }
        }

        tipo.addEventListener('change', function () {
            carregarClasses(this.value);
        });

        classe.addEventListener('change', function () {
            carregarCarrocerias(this.value);
        });

        if (tipo.value) {
            carregarClasses(
                tipo.value,
                classeSelecionada
            );
        }
    });
</script>
@endpush