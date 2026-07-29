@props([
    'session' => [],
])

<div class="grid gap-3 py-4 text-sm">
    <div class="flex min-w-0 flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 w-full flex-1">
            <div class="mt-1 block w-full overflow-hidden text-ellipsis whitespace-nowrap text-sm text-muted">
                {{ $session['agent'] ?? ($session['user-agent'] ?? 'Unknown agent') }}
            </div>
        </div>
    </div>
    <dl class="grid gap-2 text-xs sm:grid-cols-2 [&_dd]:mt-0.5 [&_dt]:text-muted">
        <x-detail label="Token Type" :value="$session['type'] ?? 'Unknown session'" mono />
        <x-detail label="IP address" :value="$session['ip'] ?? 'Unknown'" mono />
        <x-detail
            label="Created"
            :value="!empty($session['created']) && is_numeric($session['created']) ? date('M j, Y H:i T', $session['created']) : 'Unknown'"
        />
        <x-detail
            label="Expires"
            :value="!empty($session['expires']) && is_numeric($session['expires']) ? date('M j, Y H:i T', $session['expires']) : 'Unknown expiry'"
        />
    </dl>
</div>
