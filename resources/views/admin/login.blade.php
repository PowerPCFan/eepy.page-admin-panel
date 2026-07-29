@extends('layouts.admin')

@section('content')
    <x-panel class="mx-auto mt-[12vh] max-w-xl p-6 sm:p-8">
        <h1 class="text-3xl font-semibold">Sign in</h1>
        <form method="POST" action="{{ route('admin.login') }}" class="mt-7 space-y-5">
            @csrf
            <x-input label="Username" id="username" name="username" value="{{ old('username') }}"
                autocomplete="username" required autofocus placeholder="Your username" />
            <x-input label="Password" id="password" name="password" type="password"
                autocomplete="current-password" required placeholder="Your password" />
            @if (session('mfa_required') || old('mfa_required'))
                <x-input label="Two-factor authentication code" id="mfa_code" name="mfa_code"
                    inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6"
                    required placeholder="123456" />
            @endif
            <input type="hidden" id="captcha" name="captcha">
            <div id="turnstile-widget" class="cf-turnstile flex min-h-16 items-center"
                data-sitekey="{{ $turnstileSiteKey }}" data-callback="turnstileComplete"
                data-expired-callback="turnstileExpired" data-error-callback="turnstileExpired"></div>
            <x-button variant="accent" id="login-button" class="w-full" disabled>Sign in</x-button>
        </form>
    </x-panel>
    <script>
        window.turnstileComplete = function(token) {
            document.getElementById('captcha').value = token;
            document.getElementById('login-button').disabled = false;
        };
        window.turnstileExpired = function() {
            document.getElementById('captcha').value = '';
            document.getElementById('login-button').disabled = true;
        };
    </script>
@endsection
