@extends('layouts.app')

@section('content')

    @php
        $etapas = [
            'Identificação',
            'Classificação',
            'Capacidades',
            'Recursos',
            'Status',
        ];

        $tipos = [
            'Caminhão' => 'Caminhão',
            'Carreta' => 'Carreta',
            'Utilitário' => 'Utilitário',
            'Moto' => 'Moto',
        ];

        $disponibilidades = [
            'Disponível' => 'Disponível',
            'Reservado' => 'Reservado',
            'Carregando' => 'Carregando',
            'Em rota' => 'Em rota',
            'Manutencao' => 'Manutenção',
            'Indisponível' => 'Indisponível',
        ];
    @endphp

    <x-erp.cadastro.page
        :action="route('veiculos.store')"
        method="POST"
    >

        <x-slot:header>
            <x-erp.cadastro.header
                title="Cadastrar Veículo"
                subtitle="Preencha os dados operacionais e técnicos do veículo."
                icon="bi bi-truck"
                :back-url="route('veiculos.index')"
            />
        </x-slot:header>

        <x-slot:wizard>
            <x-erp.cadastro.wizard
                :steps="$etapas"
                :current="1"
            />
        </x-slot:wizard>

        <x-erp.cadastro.section
            title="Dados Básicos"
            description="Informações principais de identificação do veículo."
            icon="bi bi-card-text"
        >
            <div class="row g-3">

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <x-erp.form.input
                        name="placa"
                        label="Placa"
                        placeholder="ABC1D23"
                        required
                    />
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <x-erp.form.input
                        name="marca"
                        label="Marca"
                        placeholder="Ex.: Mercedes-Benz"
                        required
                    />
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <x-erp.form.input
                        name="modelo"
                        label="Modelo"
                        placeholder="Ex.: Atego 1719"
                        required
                    />
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <x-erp.form.input
                        name="ano_fabricacao"
                        label="Ano de fabricação"
                        type="number"
                        placeholder="2024"
                    />
                </div>

            </div>
        </x-erp.cadastro.section>

        <x-erp.cadastro.section
            title="Classificação Operacional"
            description="Classificação e condição atual do veículo."
            icon="bi bi-diagram-3"
        >
            <div class="row g-3">

                <div class="col-xl-4 col-lg-4 col-md-6">
                    <x-erp.form.select
                        name="tipo"
                        label="Tipo do veículo"
                        :options="$tipos"
                        required
                    />
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6">
                    <x-erp.form.select
                        name="disponibilidade"
                        label="Disponibilidade"
                        :options="$disponibilidades"
                        required
                    />
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6">
                    <x-erp.form.input
                        name="categoria_cnh"
                        label="Categoria mínima de CNH"
                        placeholder="Ex.: D"
                    />
                </div>

            </div>
        </x-erp.cadastro.section>

        <x-erp.cadastro.section
            title="Recursos"
            description="Recursos e características operacionais."
            icon="bi bi-tools"
        >
            <div class="row g-3">

                <div class="col-xl-4 col-md-6">
                    <x-erp.form.checkbox
                        name="possui_munck"
                        label="Possui Munck"
                        description="Veículo equipado para içamento de cargas."
                    />
                </div>

                <div class="col-xl-4 col-md-6">
                    <x-erp.form.checkbox
                        name="possui_rastreador"
                        label="Possui rastreador"
                        description="Permite acompanhamento da localização."
                    />
                </div>

                <div class="col-xl-4 col-md-6">
                    <x-erp.form.checkbox
                        name="possui_carroceria_aberta"
                        label="Carroceria aberta"
                        description="Adequado para materiais de grande volume."
                    />
                </div>

            </div>
        </x-erp.cadastro.section>

        <x-erp.cadastro.section
            title="Observações"
            description="Informações adicionais relevantes para a operação."
            icon="bi bi-chat-left-text"
        >
            <div class="row g-3">

                <div class="col-12">
                    <x-erp.form.textarea
                        name="observacao"
                        label="Observação"
                        placeholder="Informe restrições, particularidades ou recomendações..."
                        :rows="4"
                    />
                </div>

            </div>
        </x-erp.cadastro.section>

        <x-slot:actions>
            <x-erp.cadastro.actions
                :cancel-url="route('veiculos.index')"
                cancel-label="Cancelar"
                submit-label="Salvar veículo"
            />
        </x-slot:actions>

    </x-erp.cadastro.page>

@endsection