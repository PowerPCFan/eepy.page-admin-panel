@extends('layouts.admin')

@section('content')
  <x-panel class="grid gap-5 mt-10 p-6">
    <div class="grid gap-1">
      <h2 class="text-2xl font-semibold">Manual login session</h2>
      <p class="break-all text-muted text-sm">
        User: <span class="font-mono">{{ $manualLogin['user_id'] }}</span>
      </p>
    </div>

    <div class="grid gap-2">
      <label for="manual-login-snippet" class="text-sm">Paste this into the DevTools console to log in:</label>
      <pre
        id="manual-login-snippet"
        readonly
        class="min-h-40 whitespace-pre-wrap break-all rounded-lg border border-border bg-input p-3 font-mono text-xs text-foreground"
      ><code>{{ $manualLogin['snippet'] }}</code></pre>
    </div>

    <p>When you're done, terminate the session using the button below.</p>

    <form
      id="terminate-manual-session"
      method="POST"
      action="{{ route('admin.manual-session.terminate') }}"
      class="flex flex-wrap justify-end gap-3"
    >
      @csrf
      <a
        class="rounded-lg border border-border px-4 py-2 text-sm text-center"
        href="{{ route('admin.user') }}"
      >
      <x-materialsymbols size="20px" icon="person-check" class="inline" />
      Leave active</a>

      <x-confirm-modal
        id="terminate-manual-session-modal"
        form="terminate-manual-session"
        title="Terminate manual login session"
        confirm-label="Terminate session"
        icon="logout"
      >
        <x-slot:trigger>
          <x-button type="button" variant="danger">
            <x-materialsymbols size="20px" icon="logout" disabletranslatealignmentfix="true" />
            Terminate session
          </x-button>
        </x-slot:trigger>
        Are you sure you would like to terminate this manual login session and restore the account metadata?
      </x-confirm-modal>
    </form>
  </x-panel>
@endsection
