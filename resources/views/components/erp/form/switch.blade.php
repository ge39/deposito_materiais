@props([
    'name',
    'label',
    'value' => 1,
    'checked' => false,
    'description' => null,
])

@php
    $switchId = $attributes->get('id', $name);

    $isChecked = old($name) !== null
        ? (bool) old($name)
        : (bool) $checked;
@endphp

<div class="erp-check-card">

    <input
        type="hidden"
        name="{{ $name }}"
        value="0"
    >

    <div class="form-check form-switch mb-0">

        <input
            type="checkbox"
            role="switch"
            name="{{ $name }}"
            id="{{ $switchId }}"
            value="{{ $value }}"
            @checked($isChecked)
            {{ $attributes->class([
                'form-check-input',
                'is-invalid' => $errors->has($name),
            ]) }}
        >

    </div>

    <div class="erp-check-content">

        <label
            for="{{ $switchId }}"
            class="erp-check-label"
        >
            {{ $label }}
        </label>

        @if ($description)
            <span class="erp-check-description">
                {{ $description }}
            </span>
        @endif

        @error($name)
            <span class="erp-form-error">
                {{ $message }}
            </span>
        @enderror

    </div>

</div>