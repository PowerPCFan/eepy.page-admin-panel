@props(['class' => ''])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-border bg-card shadow-xl shadow-black/10 ' . $class]) }}>
    {{ $slot }}
</section>
