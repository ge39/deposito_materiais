@props([
    'cancelUrl' => null,
    'cancelLabel' => 'Cancelar',
    'submitLabel' => 'Salvar cadastro',
    'submitIcon' => 'bi bi-check-lg',
    'showSubmit' => true,
])

<footer class="erp-cadastro-actions">

    <div class="erp-cadastro-actions-left">

        @if ($cancelUrl)
            <a
                href="{{ $cancelUrl }}"
                class="erp-btn erp-btn-outline"
            >
                <i class="bi bi-arrow-left"></i>
                {{ $cancelLabel }}
            </a>
        @endif

        {{ $left ?? '' }}

    </div>

    <div class="erp-cadastro-actions-right">

        {{ $right ?? '' }}

        @if ($showSubmit)
            <button
                type="submit"
                class="erp-btn erp-btn-primary"
            >
                <i class="{{ $submitIcon }}"></i>
                {{ $submitLabel }}
            </button>
        @endif

    </div>

</footer>