<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eepy.page admin panel</title>
    @if (!($tokenSet ?? false))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-background text-foreground">
    <div class="mx-auto min-h-screen p-0 m-0 w-full h-full">
        <header class="flex items-center justify-between gap-4 bg-[#0f0f0f] px-4 sm:px-8 py-3">
            <div class="flex items-center gap-3 text-lg font-semibold">
                <img class="h-8 w-8" src="/favicon.png" alt="">
                <span>eepy.page admin panel</span>
            </div>
            @if ($tokenSet ?? false)
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <nav
                        class="flex flex-wrap items-center gap-1 rounded-lg border border-border bg-card p-1 text-sm"
                        aria-label="Admin sections"
                    >
                        <a
                            href="{{ route('admin.user') }}"
                            class="rounded-md px-3 py-1 {{ ($page ?? 'login') === 'user' ? 'bg-primary text-primary-foreground' : 'text-muted hover:text-foreground' }}"
                        >
                          <x-materialsymbols size="16px" icon="manage-accounts" class="inline mr-0.5!" />
                          User Management
                        </a>
                        <a
                            href="{{ route('admin.utilities') }}"
                            class="rounded-md px-3 py-1 {{ ($page ?? 'login') === 'utilities' ? 'bg-primary text-primary-foreground' : 'text-muted hover:text-foreground' }}"
                        >
                          <x-materialsymbols size="16px" icon="construction" class="inline mr-0.5!" />
                          Utilities
                        </a>
                    </nav>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <x-button variant="ghost" class="bg-card! px-4! py-2!">
                          <x-materialsymbols size="18px" icon="logout" disabletranslatealignmentfix="true" />
                          Log out
                        </x-button>
                    </form>
                </div>
            @endif
        </header>

        <main class="pb-16 max-w-345 px-4 sm:px-8 py-6 sm:py-7 mx-auto">
            @if (session('success'))
                <x-alert class="mt-6">{{ session('success') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="error" class="mt-6">{{ $errors->first() }}</x-alert>
            @endif
            @yield('content')
        </main>
    </div>
</body>

</html>
