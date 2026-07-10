@props([
    'name',
    'label',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
    'rows' => 4,
])

@php
    $textareaId = $attributes->get('id', $name);
    $fieldValue = old($name, $value);
@endphp

<div class="erp-form-group">

    <label
        for="{{ $textareaId }}"
        class="erp-form-label"
    >
        {{ $label }}

        @if ($required)
            <span class="erp-required">*</span>
        @endif
    </label>

    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        rows="{{ $rows }}"
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
    >{{ $fieldValue }}</textarea>

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