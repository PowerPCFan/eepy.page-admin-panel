@extends('layouts.admin')

@section('content')
  <section class="mt-10">
    <h1 class="text-3xl font-semibold sm:text-4xl">Utilities and Maintenance</h1>
    <p class="mt-2 text-sm sm:text-base text-muted">Various tools and checks to make sure everything is up and running properly!</p>
  </section>
  <x-panel class="mt-8 grid gap-4 p-6">
    {{-- kinda janky but 27px is ~2.5xl --}}
    <h1 class="font-semibold text-2xl sm:text-[27px]">DNS Desynchronization Check</h1>
    <form method="POST" action="{{ route('admin.action') }}">
      @csrf
      <input type="hidden" name="name" value="desync">
      <input type="hidden" name="user_id" value="desync">
      <x-button variant="accent">
        <x-materialsymbols icon="motion-play" />
        Run Check
      </x-button>
    </form>
    @if (session('desync'))
      @php
        $desync = session('desync');
      @endphp
      <div class="grid gap-3 border-t border-border pt-4">
        <div class="flex flex-col">
          <h2 class="text-xl font-semibold">Report</h2>
          <span class="text-sm font-light">Record Counts: MongoDB: {{ $desync['mongo_keys'] ?? 0 }}, PowerDNS: {{ $desync['powerdns_keys'] ?? 0 }}</span>
        </div>
        @foreach (($desync['issues'] ?? []) as $title => $entries)
          @if ($entries)
            @php
              $friendlyMap = [
                "duplicate_mongo" => "Duplicate records in MongoDB",
                "missing_from_powerdns" => "Missing from PowerDNS",
                "missing_from_mongo" => "Missing from MongoDB",
                "value_mismatches" => "Value mismatches",
              ];
            @endphp
            <div>
              <h3 class="text-lg font-medium">
                {{ $friendlyMap[$title] ?? str_replace('_', ' ', $title) }}
              </h3>
              <ul class="mt-1 list-disc pl-6 text-sm">
                @foreach ($entries as $entry)
                  <li><code>{{ $entry }}</code></li>
                @endforeach
              </ul>
            </div>
          @endif
        @endforeach
        @if (!collect($desync['issues'] ?? [])->flatten()->count())<p class="text-sm text-success">No desynchronization issues found.</p>@endif
      </div>
    @endif
  </x-panel>
  <x-panel class="mt-6 grid gap-4 p-6">
    <h1 class="font-semibold text-2xl sm:text-[27px]">Status Banner</h1>
    <div class="grid gap-4">
      <div class="space-y-1.5">
        <p class="text-sm">Current status</p>

        @php
          $statusIsSet = is_string($currentStatusMessage ?? null) && trim($currentStatusMessage) !== '';
        @endphp

        <p class="w-full rounded-lg text-{{ $statusIsSet ? 'foreground' : 'muted' }} outline-none transition placeholder:text-muted">
          @if ($statusIsSet)
            {{ $currentStatusMessage }}
          @else
            No status is currently set.
          @endif
        </p>
      </div>

      <form method="POST" action="{{ route('admin.action') }}" id="set-status-form">
        @csrf
        <input type="hidden" name="name" value="set-status">
        <input type="hidden" name="user_id" value="set-status">
        <div class="space-y-1.5">
          <label for="message" class="block text-sm text-foreground">Set a new message</label>
          <input
            id="message"
            name="message"
            placeholder="Enter a message..."
            class="w-full rounded-lg border border-border bg-input px-3 py-2.5 text-foreground outline-none transition placeholder:text-muted focus:border-primary focus:ring-4 focus:ring-primary/20"
          >{{ old('message') }}</input>
        </div>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
          <x-confirm-modal
            id="clear-status-modal"
            form="clear-status-form"
            title="Clear status banner"
            confirm-label="Clear status"
            confirm-variant="danger"
            icon="delete-forever"
          >
            <x-slot:trigger>
              <x-button type="button" variant="danger" class="sm:mt-0">
                <x-materialsymbols icon="cancel" />
                Remove Status
              </x-button>
            </x-slot:trigger>
            Are you sure you want to remove the current status? This will permanently remove it from eepy.page and the <x-inlinecodeblock>/status</x-inlinecodeblock> API endpoint until a new status is set.
          </x-confirm-modal>

          <x-button variant="accent" class="sm:mt-0">
            <x-materialsymbols icon="save" />
            Update Status
          </x-button>
        </div>
      </form>

      <form method="POST" action="{{ route('admin.action') }}" id="clear-status-form" class="hidden">
        @csrf
        <input type="hidden" name="name" value="clear-status">
        <input type="hidden" name="user_id" value="clear-status">
      </form>
    </div>
  </x-panel>
@endsection
