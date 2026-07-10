@props([
    'steps' => [],
    'current' => 1,
])

@php
    $currentStep = max(1, (int) $current);
@endphp

@if (count($steps) > 0)
    <nav
        class="erp-wizard"
        aria-label="Etapas do cadastro"
    >
        <div class="erp-wizard-track">

            @foreach ($steps as $index => $step)
                @php
                    $stepNumber = $index + 1;

                    $stepClass = match (true) {
                        $stepNumber < $currentStep => 'is-completed',
                        $stepNumber === $currentStep => 'is-active',
                        default => '',
                    };

                    $stepLabel = is_array($step)
                        ? ($step['label'] ?? 'Etapa ' . $stepNumber)
                        : $step;
                @endphp

                <div
                    class="erp-wizard-step {{ $stepClass }}"
                    @if ($stepNumber === $currentStep)
                        aria-current="step"
                    @endif
                >
                    <span class="erp-wizard-number">
                        @if ($stepNumber < $currentStep)
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </span>

                    <span class="erp-wizard-label">
                        {{ $stepLabel }}
                    </span>
                </div>
            @endforeach

        </div>
    </nav>
@endif