{{-- ============================================================
     WIZARD OPERACIONAL DO CADASTRO DE VEÍCULOS

     O formulário e o CSRF são controlados pelo componente:
     resources/views/components/erp/cadastro/page.blade.php
============================================================ --}}

<nav
    class="erp-wizard"
    aria-label="Etapas do cadastro do veículo"
>
    <div
        class="erp-wizard-track"
        id="veiculoWizardTabs"
        role="tablist"
    >

        <button
            type="button"
            class="erp-wizard-step is-active"
            id="tab-identificacao"
            data-bs-toggle="pill"
            data-bs-target="#pane-identificacao"
            role="tab"
            aria-controls="pane-identificacao"
            aria-selected="true"
        >
            <span class="erp-wizard-number">
                1
            </span>

            <span class="erp-wizard-label">
                Identificação
            </span>
        </button>

        <button
            type="button"
            class="erp-wizard-step"
            id="tab-capacidades"
            data-bs-toggle="pill"
            data-bs-target="#pane-capacidades"
            role="tab"
            aria-controls="pane-capacidades"
            aria-selected="false"
        >
            <span class="erp-wizard-number">
                2
            </span>

            <span class="erp-wizard-label">
                Capacidades
            </span>
        </button>

        <button
            type="button"
            class="erp-wizard-step"
            id="tab-recursos"
            data-bs-toggle="pill"
            data-bs-target="#pane-recursos"
            role="tab"
            aria-controls="pane-recursos"
            aria-selected="false"
        >
            <span class="erp-wizard-number">
                3
            </span>

            <span class="erp-wizard-label">
                Recursos
            </span>
        </button>

        <button
            type="button"
            class="erp-wizard-step"
            id="tab-restricoes"
            data-bs-toggle="pill"
            data-bs-target="#pane-restricoes"
            role="tab"
            aria-controls="pane-restricoes"
            aria-selected="false"
        >
            <span class="erp-wizard-number">
                4
            </span>

            <span class="erp-wizard-label">
                Cargas
            </span>
        </button>

        <button
            type="button"
            class="erp-wizard-step"
            id="tab-status"
            data-bs-toggle="pill"
            data-bs-target="#pane-status"
            role="tab"
            aria-controls="pane-status"
            aria-selected="false"
        >
            <span class="erp-wizard-number">
                5
            </span>

            <span class="erp-wizard-label">
                Status
            </span>
        </button>

    </div>
</nav>

<div
    class="tab-content"
    id="veiculoWizardContent"
>

    {{-- ========================================================
         ETAPA 1 — IDENTIFICAÇÃO
    ========================================================= --}}
    <div
        class="tab-pane fade show active"
        id="pane-identificacao"
        role="tabpanel"
        aria-labelledby="tab-identificacao"
        tabindex="0"
    >
        @include('veiculos.partials._identificacao')
    </div>

    {{-- ========================================================
         ETAPA 2 — CAPACIDADES E DIMENSÕES
    ========================================================= --}}
    <div
        class="tab-pane fade"
        id="pane-capacidades"
        role="tabpanel"
        aria-labelledby="tab-capacidades"
        tabindex="0"
    >
        <div class="d-flex flex-column gap-4">
            @include('veiculos.partials._capacidades')
            @include('veiculos.partials._dimensoes')
        </div>
    </div>

    {{-- ========================================================
         ETAPA 3 — RECURSOS
    ========================================================= --}}
    <div
        class="tab-pane fade"
        id="pane-recursos"
        role="tabpanel"
        aria-labelledby="tab-recursos"
        tabindex="0"
    >
        @include('veiculos.partials._recursos')
    </div>

    {{-- ========================================================
         ETAPA 4 — RESTRIÇÕES E TIPOS DE CARGA
    ========================================================= --}}
    <div
        class="tab-pane fade"
        id="pane-restricoes"
        role="tabpanel"
        aria-labelledby="tab-restricoes"
        tabindex="0"
    >
        @include('veiculos.partials._restricoes')
    </div>

    {{-- ========================================================
         ETAPA 5 — STATUS OPERACIONAL
    ========================================================= --}}
    <div
        class="tab-pane fade"
        id="pane-status"
        role="tabpanel"
        aria-labelledby="tab-status"
        tabindex="0"
    >
        @include('veiculos.partials._status')
    </div>

</div>

@push('styles')
<style>
    /*
     * Ajustes específicos do wizard interativo.
     * A identidade visual principal permanece no erp-cadastros.css.
     */

    #veiculoWizardTabs .erp-wizard-step {
        appearance: none;
        border: 0;
        background: transparent;
        padding: 0 10px;
        cursor: pointer;
    }

    #veiculoWizardTabs .erp-wizard-step:hover .erp-wizard-number {
        border-color: var(--erp-primary);
        color: var(--erp-primary);
    }

    #veiculoWizardTabs .erp-wizard-step:hover .erp-wizard-label {
        color: var(--erp-primary);
    }

    #veiculoWizardTabs .erp-wizard-step.is-active .erp-wizard-number {
        background: var(--erp-primary);
        color: #ffffff;
        border-color: var(--erp-primary);
        box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.12);
    }

    #veiculoWizardTabs .erp-wizard-step.is-active .erp-wizard-label {
        color: var(--erp-primary);
    }

    #veiculoWizardTabs .erp-wizard-step.is-completed .erp-wizard-number {
        background: var(--erp-success);
        color: #ffffff;
        border-color: var(--erp-success);
    }

    #veiculoWizardTabs .erp-wizard-step.is-completed .erp-wizard-label {
        color: var(--erp-success);
    }

    .erp-btn-outline-primary {
        color: var(--erp-primary);
        background: #ffffff;
        border: 1px solid var(--erp-primary);
    }

    .erp-btn-outline-primary:hover {
        color: #ffffff;
        background: var(--erp-primary);
        border-color: var(--erp-primary);
    }

    .erp-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    @media (max-width: 767.98px) {
        #veiculoWizardTabs .erp-wizard-step {
            min-width: 120px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wizard = document.getElementById('veiculoWizardTabs');
        const btnAnterior = document.getElementById('btnWizardAnterior');
        const btnProximo = document.getElementById('btnWizardProximo');

        if (!wizard || !btnAnterior || !btnProximo) {
            return;
        }

        const tabs = Array.from(
            wizard.querySelectorAll(
                'button[data-bs-toggle="pill"]'
            )
        );

        function getActiveIndex() {
            return tabs.findIndex(function (tab) {
                return tab.classList.contains('is-active');
            });
        }

        function atualizarEstadoVisual(activeIndex) {
            tabs.forEach(function (tab, index) {
                const isActive = index === activeIndex;
                const isCompleted = index < activeIndex;

                tab.classList.toggle('is-active', isActive);
                tab.classList.toggle('is-completed', isCompleted);

                tab.setAttribute(
                    'aria-selected',
                    isActive ? 'true' : 'false'
                );

                const numberElement = tab.querySelector(
                    '.erp-wizard-number'
                );

                if (!numberElement) {
                    return;
                }

                if (isCompleted) {
                    numberElement.innerHTML =
                        '<i class="bi bi-check-lg"></i>';
                } else {
                    numberElement.textContent = String(index + 1);
                }
            });

            btnAnterior.disabled = activeIndex <= 0;
            btnProximo.disabled = activeIndex >= tabs.length - 1;
        }

        function activateTab(index) {
            if (!tabs[index]) {
                return;
            }

            const bootstrapTab = bootstrap.Tab.getOrCreateInstance(
                tabs[index]
            );

            bootstrapTab.show();
        }

        btnAnterior.addEventListener('click', function () {
            const activeIndex = getActiveIndex();

            if (activeIndex > 0) {
                activateTab(activeIndex - 1);
            }
        });

        btnProximo.addEventListener('click', function () {
            const activeIndex = getActiveIndex();

            if (
                activeIndex >= 0 &&
                activeIndex < tabs.length - 1
            ) {
                activateTab(activeIndex + 1);
            }
        });

        tabs.forEach(function (tab, index) {
            tab.addEventListener('shown.bs.tab', function () {
                atualizarEstadoVisual(index);

                window.scrollTo({
                    top: Math.max(
                        wizard.getBoundingClientRect().top +
                        window.scrollY -
                        110,
                        0
                    ),
                    behavior: 'smooth'
                });
            });
        });

        const initialIndex = tabs.findIndex(function (tab) {
            return tab.classList.contains('is-active');
        });

        atualizarEstadoVisual(
            initialIndex >= 0 ? initialIndex : 0
        );
    });
</script>
@endpush