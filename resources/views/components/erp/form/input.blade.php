@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $fieldValue = old($name, $value);
@endphp

<div class="erp-form-group">

    <label
        for="{{ $inputId }}"
        class="erp-form-label"
    >
        {{ $label }}

        @if ($required)
            <span class="erp-required">*</span>
        @endif
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $fieldValue }}"
        @if ($placeholder)
            placeholder="{{ $placeholder }}"
        @endif
        @if ($required)
            required
        @endif
        {{ $attributes->class([
            'form-control',
            'erp-form-control',
            'is-invalid' => $errors->has($name),
        ]) }}
    >

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