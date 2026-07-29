@props([
    'label' => '',
    'value' => 'Unknown',
    'mono' => false,
])

<div>
    <dt class="text-xs text-muted">{{ $label }}</dt>
    <dd class="mt-1 {{ $mono ? 'font-mono' : '' }}">{{ $value }}</dd>
</div>
