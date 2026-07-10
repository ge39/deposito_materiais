@props([
    'title',
    'description' => null,
    'icon' => 'bi bi-grid',
    'badge' => null,
    'compact' => false,
])

<section
    {{ $attributes->class([
        'erp-cadastro-group',
        'erp-cadastro-group-compact' => $compact,
    ]) }}
>
    <header class="erp-cadastro-group-header">

        <div class="erp-cadastro-group-heading">

            <span
                class="erp-cadastro-group-icon"
                aria-hidden="true"
            >
                <i class="{{ $icon }}"></i>
            </span>

            <div class="erp-cadastro-group-title-wrapper">

                <h3 class="erp-cadastro-group-title">
                    {{ $title }}
                </h3>

                @if ($description)
                    <p class="erp-cadastro-group-description">
                        {{ $description }}
                    </p>
                @endif

            </div>

        </div>

        <div class="erp-cadastro-group-actions">

            @if ($badge)
                <span class="erp-cadastro-group-badge">
                    {{ $badge }}
                </span>
            @endif

            {{ $actions ?? '' }}

        </div>

    </header>

    <div class="erp-cadastro-group-body">
        {{ $slot }}
    </div>

</section>