@props([
    'label' => null,
    'id' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id ?? $attributes->get('name') }}" class="block text-xs text-muted">{{ $label }}</label>
    @endif
    <input id="{{ $id ?? $attributes->get('name') }}" {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border border-border bg-input px-3 py-2.5 text-foreground outline-none transition placeholder:text-muted focus:border-primary focus:ring-4 focus:ring-primary/20']) }}>
</div>
