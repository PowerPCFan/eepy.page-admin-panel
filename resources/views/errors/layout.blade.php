{{-- <!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 36px;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title">
                    @yield('message')
                </div>
            </div>
        </div>
    </body>
</html> --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $excMessage = $exception->getMessage() ?? 'No information available.';
    @endphp
    <title>@yield('code', 'Error') @yield('title', '') | eepy.page admin panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground">
    <div class="mx-auto flex min-h-screen max-w-4xl flex-col justify-center px-4 py-12">
        <div class="border border-border bg-card p-6 flex flex-col gap-3 rounded-lg">
            <div class="text-center">
                <h1 class="text-5xl font-semibold font-mono">
                    @yield('code', 'Unknown')
                </h1>
                <p class="text-xl mt-1">@yield('title', 'Error')</p>
            </div>

            <div class="min-w-0">
                <pre
                    class="mt-2 max-h-96 overflow-auto whitespace-pre-wrap wrap-break-word border border-border bg-input p-4 font-mono text-sm leading-relaxed text-foreground rounded-lg"
                >@yield('message', ''): {{ $excMessage }}</pre>
            </div>
            @php
                $buttonClasses = collect([
                    "hover:bg-primary/80", "inline-flex", "w-fit", "px-4", "py-2",
                    "font-medium", "rounded-lg", "bg-primary", "transition-all",
                ])->implode(' ');
            @endphp
            <div class="mt-3 flex gap-3">
                <a
                    href="{{ url()->previous() ?? '#' }}"
                    class="{{ $buttonClasses }}"
                >
                    Go back
                </a>
                <a
                    href="javascript:void(0)"
                    onclick="navigator.clipboard.writeText('{{ $excMessage }}')"
                    class="{{ $buttonClasses }} bg-transparent hover:bg-secondary"
                >
                    Copy response
                </a>
            </div>
        </div>
    </div>
</body>
</html>
