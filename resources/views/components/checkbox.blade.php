{{-- v1: lost --}}

{{-- v2: styling messed up --}}
{{-- @props([
    'label' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'wrapperClass' => '',
])

@if ($label !== null)
    <label class="flex items-center gap-2 text-xs text-muted {{ $wrapperClass }}">
        <input
            type="checkbox"
            @if ($name !== null) name="{{ $name }}" @endif
            value="{{ $value }}"
            @checked($checked)
            {{ $attributes->merge(['class' => 'h-4 w-4 accent-primary']) }}
        >
        {{ $label }}
    </label>
@else
    <input
        type="checkbox"
        @if ($name !== null) name="{{ $name }}" @endif
        value="{{ $value }}"
        @checked($checked)
        {{ $attributes->merge(['class' => 'h-4 w-4 accent-primary']) }}
    >
@endif --}}

{{-- v3: styling ok but tons of breaking changes + kinda janky --}}
{{-- @props([
    'label' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
])

@php
    $randomId = 'input-' . Illuminate\Support\Str::random(8);
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-row items-center gap-1.5']) }}>
    @if ($label !== null)
        <label for="{{ $randomId }}" class="text-xs text-muted">{{ $label }}</label>
        <input
            id="{{ $randomId }}"
            type="checkbox"
            @if ($name !== null) name="{{ $name }}" @endif
            value="{{ $value }}"
            @checked($checked)
            class="h-4 w-4 accent-primary"
        >
    @else
        <input
            type="checkbox"
            @if ($name !== null) name="{{ $name }}" @endif
            value="{{ $value }}"
            @checked($checked)
        >
    @endif
</div> --}}

{{-- v4: current working ver --}}
@props([
    'label' => null,
    'name' => null,
    'value' => '1',
    'checked' => false,
    'id' => null,
    'containerClass' => '',
])

@php
    $inputId = $id ?? ($label ? 'input-' . Illuminate\Support\Str::random(8) : null);
@endphp

<div class="flex flex-row items-center gap-1.5 {{ $containerClass }}">
    <input
        @if ($inputId) id="{{ $inputId }}" @endif
        type="checkbox"
        @if ($name !== null) name="{{ $name }}" @endif
        value="{{ $value }}"
        @checked($checked)
        {{ $attributes->merge(['class' => 'h-4 w-4 accent-primary']) }}
    />

    @if ($label !== null)
        <label for="{{ $inputId }}" class="text-xs text-muted">{{ $label }}</label>
    @endif
</div>
