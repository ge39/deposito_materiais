@props([
    'action',
    'method' => 'POST',
    'enctype' => null,
])

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/erp-cadastros.css') }}">
    @endpush
@endonce

@php
    $formMethod = strtoupper($method);
    $htmlMethod = in_array($formMethod, ['GET', 'POST'], true)
        ? $formMethod
        : 'POST';
@endphp

<div class="erp-cadastro-page">
    <div class="erp-cadastro-container">

        {{ $header ?? '' }}

        {{ $wizard ?? '' }}

        <x-erp.cadastro.alert-errors />

        <form
            action="{{ $action }}"
            method="{{ $htmlMethod }}"
            class="erp-cadastro-form"
            @if ($enctype)
                enctype="{{ $enctype }}"
            @endif
            {{ $attributes }}
        >
            @if ($htmlMethod !== 'GET')
                @csrf
            @endif

            @if (! in_array($formMethod, ['GET', 'POST'], true))
                @method($formMethod)
            @endif

            <div class="erp-cadastro-content">
                {{ $slot }}
            </div>

            {{ $actions ?? '' }}
        </form>

    </div>
</div>