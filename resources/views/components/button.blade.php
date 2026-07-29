@props([
  'variant' => 'primary',
  'size' => 'default',
  'type' => 'submit',
])

@php
  $variants = [
    'primary' => 'bg-foreground text-background hover:bg-primary-strong',
    'accent' => 'bg-primary text-white hover:bg-primary-strong',
    'ghost' => 'border border-border bg-transparent text-foreground hover:border-primary hover:bg-secondary',
    'danger' => 'bg-destructive text-white hover:brightness-110',
  ];
  $sizes = [
    'default' => 'px-3.5 py-2.5 text-sm',
    'small' => 'px-2.5 py-2 text-xs',
  ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex cursor-pointer items-center justify-center rounded-lg font-semibold transition disabled:cursor-not-allowed disabled:opacity-50 ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['default'])]) }}>
  {{ $slot }}
</button>
