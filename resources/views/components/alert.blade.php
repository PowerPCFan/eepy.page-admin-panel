@props(['variant' => 'success'])

@php
    $classes = $variant === 'error'
        ? 'bg-alert text-alert-foreground'
        : 'bg-success text-success-foreground';
@endphp

<div role="status" {{ $attributes->merge(['class' => 'rounded-lg px-4 py-3 text-sm ' . $classes]) }}>
    {{ $slot }}
</div>
