@extends('layouts.admin')

@section('content')
    <h1 class="mt-10 mb-8 text-3xl font-semibold">Component test</h1>

    <h2 class="mb-3 text-xl font-semibold">Alerts</h2>
    <x-alert class="mb-3">Success alert example.</x-alert>
    <x-alert variant="error" class="mb-8">Error alert example.</x-alert>

    <h2 class="mb-3 text-xl font-semibold">Buttons</h2>
    <x-button type="button" variant="primary" class="mb-3 mr-2">Primary default</x-button>
    <x-button type="button" variant="accent" class="mb-3 mr-2">Accent default</x-button>
    <x-button type="button" variant="ghost" class="mb-3 mr-2">Ghost default</x-button>
    <x-button type="button" variant="danger" class="mb-3 mr-2">Danger default</x-button>
    <br>
    <x-button type="button" variant="primary" size="small" class="mb-3 mr-2">Primary small</x-button>
    <x-button type="button" variant="accent" size="small" class="mb-3 mr-2">Accent small</x-button>
    <x-button type="button" variant="ghost" size="small" class="mb-3 mr-2">Ghost small</x-button>
    <x-button type="button" variant="danger" size="small" class="mb-3 mr-2">Danger small</x-button>
    <br>
    <x-button type="button" disabled class="mb-8 mr-2">Disabled button</x-button>

    <h2 class="mb-3 text-xl font-semibold">Checkboxes</h2>
    <x-checkbox name="unchecked" value="yes" label="Unchecked labeled checkbox" containerClass="mb-3" />
    <x-checkbox name="checked" value="yes" label="Checked labeled checkbox" checked containerClass="mb-3" />
    <x-checkbox name="disabled" value="yes" label="Disabled labeled checkbox" disabled containerClass="mb-3" />
    <x-checkbox name="unlabeled" value="yes" containerClass="mb-8 mr-2" />

    <h2 class="mb-3 text-xl font-semibold">Inputs</h2>
    <x-input name="plain-input" placeholder="Plain input" class="mb-3" />
    <x-input name="labeled-input" id="labeled-input" label="Labeled input" value="Example value" class="mb-3" />
    <x-input name="required-input" label="Required input" placeholder="Required example" required class="mb-8" />

    <h2 class="mb-3 text-xl font-semibold">Panels</h2>
    <x-panel class="mb-3 p-4">Panel example.</x-panel>

    <h2 class="mb-3 text-xl font-semibold">Details</h2>
    <dl class="mb-8">
        <x-detail label="Regular detail" value="Readable example value" />
        <x-detail label="Monospace detail" value="example-token-123" mono />
    </dl>

    <h2 class="mb-3 text-xl font-semibold">Session row</h2>
    <div class="mb-8 border-y border-border">
        <x-session-row :session="$testSession" />
    </div>

    <h2 class="mb-3 text-xl font-semibold">Confirmation modals</h2>
    <form id="component-test-form" class="mb-3" onsubmit="event.preventDefault(); document.getElementById('component-test-result').textContent = 'Confirmed';">
        <x-confirm-modal
            id="component-test-modal"
            form="component-test-form"
            title="Example confirmation"
            confirm-label="Confirm example"
            confirm-variant="danger"
        >
            <x-slot:trigger><x-button type="button" variant="accent">Open modal with form</x-button></x-slot:trigger>
            This is example modal content. The checkbox is deliberately inside the modal.
            <x-checkbox form="component-test-form" name="send_email" label="Send email" containerClass="mt-3" />
        </x-confirm-modal>
    </form>
    <p id="component-test-result" class="mb-3 text-sm text-muted">Nothing confirmed yet.</p>
    <x-confirm-modal
        id="component-test-modal-no-form"
        title="Example modal without form"
        confirm-label="Close example"
        confirm-variant="ghost"
    >
        <x-slot:trigger><x-button type="button" variant="ghost">Open modal without form</x-button></x-slot:trigger>
        This modal demonstrates the generic component without attaching its confirm button to a form.
    </x-confirm-modal>
@endsection
