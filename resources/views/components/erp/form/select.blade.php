@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => 'Selecione...',
    'help' => null,
])

@php
    $selectId = $attributes->get('id', $name);
    $selectedValue = old($name, $value);
@endphp

<div class="erp-form-group">

    <label
        for="{{ $selectId }}"
        class="erp-form-label"
    >
        {{ $label }}

        @if ($required)
            <span class="erp-required">*</span>
        @endif
    </label>

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        @if ($required)
            required
        @endif
        {{ $attributes->class([
            'form-select',
            'erp-form-select',
            'is-invalid' => $errors->has($name),
        ]) }}
    >
        @if ($placeholder !== false)
            <option value="">
                {{ $placeholder }}
            </option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option
                value="{{ $optionValue }}"
                @selected((string) $selectedValue === (string) $optionValue)
            >
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if ($help)
        <small class="erp-form-help">
            {{ $help }}
        </small>
    @endif

    @error($name)
        <span class="erp-form-error">
            {{ $message }}
        </span>
    @enderror

</div>