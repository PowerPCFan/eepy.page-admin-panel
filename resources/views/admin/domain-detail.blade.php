@extends('layouts.admin')

@section('content')
  @php
    $domain = $domainDetail['domain'] ?? [];
    $values = $domain['ip'] ?? [];
    $values = is_array($values) ? $values : [$values];
    $userId = $domainDetail['user_id'] ?? '';
  @endphp

  <div class="my-10 flex flex-wrap items-end justify-between gap-4">
    <div>
      @php
        $domainId = \App\Helpers\Helpers::slugifyDomainForURL($domain['name']);
      @endphp
      <x-button
        type="button"
        variant="ghost"
        size="small"
        onclick="window.location.href = '{{ route('admin.user') }}#{{ $domainId }}'"
      >
        <x-materialsymbols size="16px" icon="arrow-back" />
        Back
      </x-button>
      <h1 class="mt-3 text-3xl font-semibold">Domain editor</h1>
      <p class="mt-1 break-all text-sm text-muted">
        Domain: <span class="font-mono">{{ $domain['name'] ?? 'Unknown domain' }}</span>
      </p>
      <p class="break-all text-sm text-muted">
        Owner: <span class="font-mono text-xs">{{ $userId }}</span>
      </p>
    </div>
  </div>

  <x-panel class="grid gap-6 p-6">
    <form id="edit-domain" method="POST" action="{{ route('admin.action') }}" class="grid gap-5">
      @csrf
      <input type="hidden" name="name" value="edit-domain">
      <input type="hidden" name="user_id" value="{{ $userId }}">
      <input type="hidden" name="domain" value="{{ $domain['name'] ?? '' }}">
      <input type="hidden" name="old_type" value="{{ $domain['type'] ?? '' }}">

      <div class="grid gap-2 sm:grid-cols-2">
        <x-input
          name="type"
          label="Record type"
          value="{{ $domain['type'] ?? '' }}"
          {{-- component trick to get the mono font on just the input, not the label --}}
          style="font-family: var(--font-mono)"
          required
        />
        <div class="grid gap-2">
          <label for="edit-mode" class="text-xs text-muted">Update target</label>
          <select
            id="edit-mode"
            name="mode"
            required
            class="w-full rounded-lg border border-border bg-input px-3 py-2.5 text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20"
          >
            <option value="both">Synchronized &lpar;MongoDB + PowerDNS&rpar;</option>
            <option value="mongo">MongoDB only</option>
            <option value="pdns">PowerDNS only</option>
          </select>
        </div>
      </div>

      <div class="grid gap-3">
        <div>
          <h2 class="font-semibold">Record values</h2>
        </div>
        <div class="grid gap-3">
          @foreach ($values as $value)
            <x-input
              name="values[]"
              label="Value {{ $loop->iteration }}"
              value="{{ $value }}"
              {{-- component trick to get the mono font on just the input, not the label --}}
              style="font-family: var(--font-mono)"
              required
            />
          @endforeach
        </div>
      </div>

      <div class="flex flex-wrap justify-end gap-3">
        <x-button
          type="button"
          variant="ghost"
          onclick="window.location.href = '{{ route('admin.user') }}'"
        >
          <x-materialsymbols icon="cancel" size="20px" disabletranslatealignmentfix="true" />
          Cancel
        </x-button>
        <x-confirm-modal
          id="edit-domain-modal"
          form="edit-domain"
          title="Apply domain changes"
          confirm-label="Apply changes"
          confirm-variant="accent"
          icon="check"
        >
          <x-slot:trigger>
            <x-button type="button" variant="accent">
              <x-materialsymbols size="20px" icon="check" disabletranslatealignmentfix="true" />
              Apply domain changes
            </x-button>
          </x-slot:trigger>
          Are you sure you would like to apply these changes to this domain?
        </x-confirm-modal>
      </div>
    </form>
  </x-panel>
@endsection
