@props([
    'title' => 'Confirm action',
    'form' => null,
    'confirmLabel' => 'Confirm',
    'confirmVariant' => 'danger',
])

@php
  $modalId = $attributes->get('id', 'confirm-modal-' . uniqid());
@endphp

<div {{ $attributes->except('id')->merge(['class' => 'contents']) }}>
  <span data-confirm-modal-trigger="{{ $modalId }}">
    {{ $trigger }}
  </span>

  <dialog id="{{ $modalId }}"
    class="m-auto w-[min(100%-2rem,32rem)] rounded-2xl border border-border bg-card p-0 text-foreground shadow-2xl backdrop:bg-black/60"
    aria-labelledby="{{ $modalId }}-title">
    <div class="grid gap-5 p-6">
      <div>
        <h2 id="{{ $modalId }}-title" class="text-xl font-semibold">{{ $title }}</h2>
        <div class="mt-2 text-sm text-muted">{{ $slot }}</div>
      </div>
      <div class="flex flex-wrap justify-end gap-3">
        <x-button type="button" variant="ghost" data-confirm-modal-cancel="{{ $modalId }}">Cancel</x-button>
        @if ($form)
          <x-button type="submit" variant="{{ $confirmVariant }}" form="{{ $form }}"
            data-confirm-modal-confirm="{{ $modalId }}">{{ $confirmLabel }}</x-button>
        @else
          <x-button type="submit" variant="{{ $confirmVariant }}"
            data-confirm-modal-confirm="{{ $modalId }}">{{ $confirmLabel }}</x-button>
        @endif
      </div>
    </div>
  </dialog>
</div>

<script>
  (() => {
    const dialog = document.getElementById(@json($modalId));
    const trigger = document.querySelector('[data-confirm-modal-trigger="' + @json($modalId) +
      '"]');
    const cancel = dialog?.querySelector('[data-confirm-modal-cancel="' + @json($modalId) + '"]');

    trigger?.addEventListener('click', () => dialog?.showModal());
    cancel?.addEventListener('click', () => dialog?.close());
    dialog?.addEventListener('click', event => {
      if (event.target === dialog) dialog.close();
    });
  })();
</script>
