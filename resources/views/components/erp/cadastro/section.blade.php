@props([
    'title',
    'description' => null,
    'icon' => 'bi bi-grid',
])

<section class="erp-section">

    <header class="erp-section-header">

        <div class="erp-section-icon" aria-hidden="true">
            <i class="{{ $icon }}"></i>
        </div>

        <div class="erp-section-heading">
            <h2 class="erp-section-title">
                {{ $title }}
            </h2>

            @if ($description)
                <p class="erp-section-description">
                    {{ $description }}
                </p>
            @endif
        </div>

        @isset($headerActions)
            <div class="ms-auto">
                {{ $headerActions }}
            </div>
        @endisset

    </header>

    <div class="erp-section-body">
        {{ $slot }}
    </div>

</section>