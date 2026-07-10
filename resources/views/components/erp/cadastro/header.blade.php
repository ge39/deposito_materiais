@props([
    'title',
    'subtitle' => null,
    'icon' => 'bi bi-pencil-square',
    'backUrl' => null,
    'backLabel' => 'Voltar',
])

<header class="erp-cadastro-header">

    <div class="erp-cadastro-header-main">

        <div class="erp-cadastro-header-icon" aria-hidden="true">
            <i class="{{ $icon }}"></i>
        </div>

        <div class="erp-cadastro-header-text">
            <h1 class="erp-cadastro-title">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="erp-cadastro-subtitle">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

    </div>

    <div class="erp-cadastro-header-actions">

        {{ $actions ?? '' }}

        @if ($backUrl)
            <a
                href="{{ $backUrl }}"
                class="erp-btn erp-btn-outline"
            >
                <i class="bi bi-arrow-left"></i>
                {{ $backLabel }}
            </a>
        @endif

    </div>

</header>