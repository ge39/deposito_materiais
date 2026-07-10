@props([
    'name',
    'label',
    'value' => 1,
    'checked' => false,
    'description' => null,
])

@php
    $checkboxId = $attributes->get('id', $name);

    $isChecked = old($name) !== null
        ? (bool) old($name)
        : (bool) $checked;
@endphp

<label
    for="{{ $checkboxId }}"
    class="erp-check-card"
>

    <input
        type="hidden"
        name="{{ $name }}"
        value="0"
    >

    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $checkboxId }}"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->class([
            'form-check-input',
            'is-invalid' => $errors->has($name),
        ]) }}
    >

    <span class="erp-check-content">

        <span class="erp-check-label">
            {{ $label }}
        </span>

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

    </span>

</label>