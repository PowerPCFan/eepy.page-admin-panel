<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/favicon.png">
  <title>eepy.page admin panel</title>
  @if (!($tokenSet ?? false))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  @endif
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-background text-foreground">
  <div class="mx-auto min-h-screen p-0 m-0 w-full h-full">
    <header class="flex items-center justify-between gap-4 bg-[#0f0f0f] px-4 sm:px-8 py-3">
      <a href="/" class="flex items-center gap-3 text-lg font-semibold text-foreground">
        <img class="h-8 w-8" src="/favicon.png" alt="eepy.page logo" />
        <span class="hidden xs:block">eepy.page admin panel</span>
      </a>

      @if ($tokenSet ?? false)
        <div class="flex items-center">
          <button id="mobile-menu-button" class="md:hidden text-white focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <nav class="hidden md:flex flex-wrap items-center gap-2 rounded-lg border border-border bg-card p-1 text-sm" aria-label="Admin sections">
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
          <form method="POST" action="{{ route('admin.logout') }}" class="hidden md:block ml-2">
            @csrf
            <x-button variant="ghost" class="bg-card! px-4! py-2!">
              <x-materialsymbols size="18px" icon="logout" disabletranslatealignmentfix="true" />
              Log out
            </x-button>
          </form>
        </div>
      @endif
    </header>

    <div id="mobile-menu-backdrop" class="fixed inset-0 bg-black opacity-0 hidden z-40 transition-opacity duration-300 ease-in-out"></div>
    <div id="mobile-menu" class="fixed top-0 right-0 h-full w-64 bg-[#0f0f0f] shadow-lg z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden md:hidden">
      <div class="p-4 flex justify-end">
        <button id="close-mobile-menu" class="text-white focus:outline-none">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="flex flex-col gap-2 p-4">
        <a
          href="{{ route('admin.user') }}"
          class="rounded-md px-3 py-2 text-left {{ ($page ?? 'login') === 'user' ? 'bg-primary text-primary-foreground' : 'text-muted hover:text-foreground' }}"
        >
          <x-materialsymbols size="16px" icon="manage-accounts" class="inline mr-0.5!" />
          User Management
        </a>
        <a
          href="{{ route('admin.utilities') }}"
          class="rounded-md px-3 py-2 text-left {{ ($page ?? 'login') === 'utilities' ? 'bg-primary text-primary-foreground' : 'text-muted hover:text-foreground' }}"
        >
          <x-materialsymbols size="16px" icon="construction" class="inline mr-0.5!" />
          Utilities
        </a>
        <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
          @csrf
          <x-button variant="ghost" class="w-full bg-card! px-4! py-2! text-left">
            <x-materialsymbols size="18px" icon="logout" disabletranslatealignmentfix="true" />
            Log out
          </x-button>
        </form>
      </div>
    </div>

    <main class="pb-16 max-w-345 px-4 sm:px-8 py-6 sm:py-7 mx-auto">
      @if (session('success'))
        <x-alert variant="success" class="mt-2">
          {{ session('success') }}
        </x-alert>
      @endif
      @if ($errors->any())
        <x-alert variant="error" class="mt-2">
          {{ $errors->first() }}
        </x-alert>
      @endif
      @yield('content')
    </main>
  </div>

  <script>
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeMobileMenuButton = document.getElementById('close-mobile-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');

    const closeMobileMenu = () => {
      mobileMenu.classList.remove('translate-x-0');
      mobileMenu.classList.add('translate-x-full');
      mobileMenuBackdrop.classList.remove('opacity-100');
      mobileMenuBackdrop.classList.add('opacity-0');
      setTimeout(() => {
        if (!mobileMenu.classList.contains('translate-x-0')) {
          mobileMenu.classList.add('hidden');
          mobileMenuBackdrop.classList.add('hidden');
        }
      }, 300);
    };

    if (mobileMenuButton && mobileMenu && mobileMenuBackdrop) {
      mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.remove('hidden');
        mobileMenu.classList.remove('translate-x-full');
        mobileMenu.classList.add('translate-x-0');
        mobileMenuBackdrop.classList.remove('hidden');
        mobileMenuBackdrop.classList.add('opacity-100');
      });

      if (closeMobileMenuButton) {
        closeMobileMenuButton.addEventListener('click', closeMobileMenu);
      }

      mobileMenuBackdrop.addEventListener('click', closeMobileMenu);
    }
  </script>
</body>

</html>
