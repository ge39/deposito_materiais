@extends('layouts.app')

@section('title', 'Editar Veículo')

@section('content')

    <x-erp.cadastro.page
        :action="route('veiculos.update', $veiculo)"
        method="PUT"
        autocomplete="off"
    >

        {{-- =====================================================
             CABEÇALHO UNIVERSAL
        ====================================================== --}}
        <x-slot:header>
            <x-erp.cadastro.header
                title="Editar Veículo"
                subtitle="Atualize os dados operacionais do veículo para manter a frota consistente."
                icon="bi bi-pencil-square"
                :back-url="route('veiculos.index')"
                back-label="Voltar para veículos"
            >
                <x-slot:actions>

                    <a
                        href="{{ route('veiculos.show', $veiculo) }}"
                        class="erp-btn erp-btn-outline-primary"
                    >
                        <i class="bi bi-eye"></i>
                        Ver detalhes
                    </a>

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                        Fase 3 · Logística
                    </span>

                </x-slot:actions>
            </x-erp.cadastro.header>
        </x-slot:header>

        {{-- =====================================================
             CAMPOS E ETAPAS DO CADASTRO
        ====================================================== --}}
        @include('veiculos.partials._form')

        {{-- =====================================================
             RODAPÉ UNIVERSAL
        ====================================================== --}}
        <x-slot:actions>
            <x-erp.cadastro.actions
                :cancel-url="route('veiculos.index')"
                cancel-label="Voltar"
                :show-submit="false"
            >
                <x-slot:right>

                    <button
                        type="button"
                        class="erp-btn erp-btn-outline"
                        id="btnWizardAnterior"
                    >
                        <i class="bi bi-chevron-left"></i>
                        Anterior
                    </button>

                    <button
                        type="button"
                        class="erp-btn erp-btn-outline-primary"
                        id="btnWizardProximo"
                    >
                        Próximo
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <button
                        type="submit"
                        class="erp-btn erp-btn-primary"
                    >
                        <i class="bi bi-check-circle"></i>
                        Salvar Alterações
                    </button>

                </x-slot:right>
            </x-erp.cadastro.actions>
        </x-slot:actions>

    </x-erp.cadastro.page>

@endsection