@props([
  'title'
])

<div class="rounded-lg border border-border p-3">
  <dt class="text-xs text-muted">{{ $title }}</dt>
  <dd class="mt-0.5">{{ $slot }}</dd>
</div>
