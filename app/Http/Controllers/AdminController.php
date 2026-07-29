<?php

namespace App\Http\Controllers;

use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class AdminController extends Controller
{
    private const TLDs = ['eepy.page', 'worksonmymachine.top'];

    private const TOKEN_RENEWAL_LEEWAY_SECONDS = 60;

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('api_token')) {
            return redirect()->route('admin.user');
        }

        return view('admin.login', [
            'page' => 'login',
            'users' => $request->session()->get('users', []),
            'tokenSet' => $request->session()->has('api_token'),
            'apiUrl' => config('services.eepy.url'),
            'turnstileSiteKey' => config('services.eepy.turnstile_site_key'),
            'tlds' => self::TLDs,
        ]);
    }

    public function user(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureAuthenticated($request)) {
            return $redirect;
        }

        $this->refreshLoadedUsers($request);

        return view('admin.user', $this->viewData($request, 'user'));
    }

    public function utilities(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureAuthenticated($request)) {
            return $redirect;
        }

        return view('admin.utilities', $this->viewData($request, 'utilities'));
    }

    public function componentTest(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureAuthenticated($request)) {
            return $redirect;
        }

        return view('admin.component-test', [
            ...$this->viewData($request, 'component-test'),
            'testSession' => [
                'type' => 'REFRESH',
                'ip' => '192.168.1.1',
                'created' => 1760000000,
                'expires' => 1760086400,
                'agent' => 'eepy.page admin panel component test/1.0',
            ],
        ]);
    }

    public function domainDetail(Request $request, string $userId, string $domain, ?string $type = null): View|RedirectResponse
    {
        if ($redirect = $this->ensureAuthenticated($request)) {
            return $redirect;
        }

        $detail = $this->api($request, 'GET', '/admin/domain/detail', query: array_filter([
            'user_id' => $userId,
            'domain' => $domain,
            'type' => $type,
        ]));

        return view('admin.domain-detail', [
            ...$this->viewData($request, 'domain'),
            'domainDetail' => $detail,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:4096'],
            'captcha' => ['required', 'string', 'max:4096'],
            'mfa_code' => ['nullable', 'digits:6'],
        ]);

        $response = $this->backendRequest()
            ->withHeaders([
                'X-Captcha-Code' => $validated['captcha'],
                ...array_filter(['X-MFA-Code' => $validated['mfa_code'] ?? null]),
            ])
            ->timeout(20)
            ->post('/login', [
                'username_hash' => hash('sha256', $validated['username']),
                'plain_username' => $validated['username'],
                'password' => $validated['password'],
            ]);

        if ($response->status() === 412) {
            return back()->withInput([
                'username' => $validated['username'],
                'mfa_required' => true,
            ])->withErrors(['login' => 'Enter the six-digit code from your authenticator app.']);
        }

        if ($response->status() === 401 || $response->status() === 404) {
            return back()->withInput(['username' => $validated['username']])
                ->withErrors(['login' => 'Username and password do not match.']);
        }

        if ($response->status() === 403) {
            return back()->withInput(['username' => $validated['username']])
                ->withErrors(['login' => 'This account must be verified before it can sign in.']);
        }

        if ($response->status() === 429) {
            return back()->withInput(['username' => $validated['username']])
                ->withErrors(['login' => 'Captcha verification failed. Please try again.']);
        }

        if ($response->failed() || ! is_string($response->json('auth-token'))) {
            Log::warning('Backend login failed', ['status' => $response->status()]);

            return back()->withInput(['username' => $validated['username']])
                ->withErrors(['login' => 'The backend could not complete the sign-in.']);
        }

        $request->session()->regenerate();
        $request->session()->put('api_token', $response->json('auth-token'));

        $refreshToken = $this->refreshTokenFromResponse($response);
        if ($refreshToken === null) {
            $request->session()->invalidate();

            return back()->withInput(['username' => $validated['username']])
                ->withErrors(['login' => 'The backend did not provide a refresh token.']);
        }
        $request->session()->put('api_refresh_token', $refreshToken);

        try {
            $this->api($request, 'GET', '/admin/user/can-access');
        } catch (\Throwable) {
            $this->revokeBackendSession($request);
            $request->session()->invalidate();

            return back()->withErrors(['login' => 'Your account does not have admin permission.']);
        }

        return to_route('admin.index')->with('success', 'Admin access confirmed.');
    }

    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:username,id,domain,email,referral,ips'],
            'term' => ['required', 'string', 'max:2048'],
            'replace' => ['nullable', 'boolean'],
        ]);

        $path = match ($validated['type']) {
            'username' => '/admin/user/get/username',
            'id' => '/admin/user/get/id',
            'domain' => '/admin/user/get/domain',
            'email' => '/admin/user/get/email',
            'referral' => '/admin/user/get/referral',
            'ips' => '/admin/user/get/ips',
        };

        try {
            $response = $validated['type'] === 'ips'
                ? $this->api($request, 'POST', $path, body: ['ips' => preg_split('/\R/', $validated['term'], -1, PREG_SPLIT_NO_EMPTY)])
                : $this->api($request, 'GET', $path, query: [$validated['type'] === 'id' ? 'id' : $validated['type'] => $validated['term']]);
        } catch (HttpExceptionInterface $exception) {
            if ($exception->getStatusCode() !== 404) {
                throw $exception;
            }

            $response = null;
        }

        $results = $response === null ? [] : ($validated['type'] === 'ips' ? $response : [$response]);
        $users = ($validated['replace'] ?? true) ? $results : [...$request->session()->get('users', []), ...$results];
        $request->session()->put('users', $users);

        return to_route('admin.user')->with('success', count($results).' '.str('account')->plural(count($results)).' loaded.');
    }

    public function action(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'in:verify,permission,permissions,add-tld,remove-tld,delete-domain,edit-domain,delete-account,reinstate,full-admin,manual-login,desync'],
            'user_id' => ['required', 'string', 'max:255'],
            'permission' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:2048'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'string', 'in:true,false'],
            'tld' => ['nullable', 'in:'.implode(',', self::TLDs)],
            'domain' => ['nullable', 'required_if:name,edit-domain', 'string', 'max:255'],
            'type' => ['nullable', 'required_if:name,edit-domain', 'string', 'in:A,AAAA,CNAME,TXT'],
            'old_type' => ['nullable', 'string', 'in:A,AAAA,CNAME,TXT'],
            'mode' => ['nullable', 'string', 'in:both,mongo,pdns'],
            'values' => ['nullable', 'required_if:name,edit-domain', 'array', 'min:1'],
            'values.*' => ['required', 'string', 'max:4096'],
            'reason' => ['nullable', 'string', 'max:2048'],
            'reasons' => ['nullable', 'string', 'max:4096'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $name = $validated['name'];
        match ($name) {
            'verify' => $this->api($request, 'POST', '/admin/user/verify', query: ['id' => $validated['user_id']]),
            'permission' => $this->api($request, 'PATCH', '/admin/user/permission', query: [
                'id' => $validated['user_id'],
                'permission' => $validated['permission'],
                'value' => $this->castPermissionValue($validated['value'] ?? ''),
                'send_email' => $validated['send_email'] ?? false,
            ]),
            'permissions' => $this->updatePermissions($request, $validated['user_id'], $validated['permissions'] ?? [], $validated['send_email'] ?? false),
            'add-tld', 'remove-tld' => $this->api($request, 'POST', '/admin/user/tld/'.($name === 'add-tld' ? 'add' : 'remove'), query: [
                'id' => $validated['user_id'],
                'tld' => $validated['tld'],
                'send_email' => $validated['send_email'] ?? false,
            ]),
            'delete-domain' => $this->api($request, 'DELETE', '/admin/domain/delete', query: [
                'userid' => $validated['user_id'],
                'domain' => $validated['domain'],
                'type' => $validated['type'],
                'reason' => $validated['reason'] ?? '',
                'send_email' => $validated['send_email'] ?? false,
            ]),
            'edit-domain' => $this->api($request, 'PATCH', '/admin/domain/edit', body: [
                'user_id' => $validated['user_id'],
                'domain' => $validated['domain'],
                'values' => array_values(array_filter($validated['values'] ?? [], static fn (string $value): bool => trim($value) !== '')),
                'type' => $validated['type'],
                'old_type' => $validated['old_type'] ?? null,
                'mode' => $validated['mode'] ?? 'both',
            ]),
            'delete-account' => $this->api($request, 'DELETE', '/admin/user/delete', body: [
                'user_id' => $validated['user_id'],
                'reasons' => preg_split('/\R/', $validated['reasons'] ?? '', -1, PREG_SPLIT_NO_EMPTY),
                'send_email' => $validated['send_email'] ?? false,
            ]),
            'reinstate' => $this->api($request, 'POST', '/admin/user/reinstate', query: [
                'user_id' => $validated['user_id'],
                'send_email' => $validated['send_email'] ?? false,
            ]),
            'full-admin' => $this->api($request, 'POST', '/admin/user/full-admin', body: ['user_id' => $validated['user_id']]),
            'manual-login' => $this->manualLogin($request, $validated['user_id']),
            'desync' => $this->desync($request),
        };

        $route = $name === 'desync' ? 'admin.utilities' : 'admin.user';

        if ($name === 'manual-login') {
            return to_route('admin.manual-session');
        }

        if ($route === 'admin.user') {
            $this->refreshLoadedUsers($request);
        }

        $readableMap = [
            'remove-tld' => 'Successfully removed TLD.',
            'add-tld' => 'Successfully added TLD.',
            'desync' => 'DNS desynchronization check completed.',
        ];

        return to_route($route)->with('success', $readableMap[$name] ?? (ucfirst(str_replace('-', ' ', $name)).' completed.'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->revokeBackendSession($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('admin.index');
    }

    public function manualSession(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureAuthenticated($request)) {
            return $redirect;
        }

        $manualLogin = $request->session()->get('manual_login');
        if (! is_array($manualLogin)) {
            return redirect()->route('admin.user');
        }

        return view('admin.manual-session', [
            ...$this->viewData($request, 'manual-session'),
            'manualLogin' => $manualLogin,
        ]);
    }

    public function terminateManualSession(Request $request): RedirectResponse
    {
        $manualLogin = $request->session()->get('manual_login');
        if (! is_array($manualLogin) || ! is_string($manualLogin['refresh_token'] ?? null)) {
            return to_route('admin.user')->withErrors(['manual_login' => 'There is no active manual login session.']);
        }

        $this->api($request, 'POST', '/admin/user/manual-login/terminate', body: [
            'user_id' => $manualLogin['user_id'],
            'refresh_token' => $manualLogin['refresh_token'],
        ]);
        $request->session()->forget('manual_login');
        $this->refreshLoadedUsers($request);

        return to_route('admin.user')->with('success', 'Manual login session terminated.');
    }

    private function api(Request $request, string $method, string $path, array $query = [], array $body = [], bool $retry = true): mixed
    {
        $token = $request->session()->get('api_token');
        abort_unless($token, 401, 'An API token is required.');

        $response = $this->backendRequest()
            ->withHeaders(['X-Auth-Token' => $token, 'Accept' => 'application/json'])
            ->timeout(20)
            ->send($method, $path, ['query' => $query, 'json' => $body]);

        if ($response->status() === 460 && $retry && $this->renewToken($request)) {
            return $this->api($request, $method, $path, $query, $body, false);
        }

        if ($response->status() === 401 || $response->status() === 460) {
            $request->session()->invalidate();
            throw new HttpResponseException(
                to_route('admin.index')->withErrors(['login' => 'Your session expired. Please sign in again.'])
            );
        }

        if ($response->status() === 461 || $response->status() === 403) {
            abort(403, $this->formatApiError($response));
        }

        if ($response->failed()) {
            Log::warning('Admin API request failed', ['path' => $path, 'status' => $response->status()]);
            abort($response->status(), $this->formatApiError($response));
        }

        return $response->json();
    }

    private function renewToken(Request $request): bool
    {
        $refreshToken = $request->session()->get('api_refresh_token');
        if (! is_string($refreshToken) || $refreshToken === '') {
            return false;
        }

        $response = $this->backendRequest()
            ->withCookies([$this->backendCookieName() => $refreshToken], $this->backendCookieDomain())
            ->timeout(20)
            ->post('/refresh');

        if (! $response->successful() || ! is_string($response->json('auth-token'))) {
            return false;
        }

        $request->session()->put('api_token', $response->json('auth-token'));
        $newRefreshToken = $this->refreshTokenFromResponse($response);
        if ($newRefreshToken !== null) {
            $request->session()->put('api_refresh_token', $newRefreshToken);
        }

        return true;
    }

    private function ensureAuthenticated(Request $request): ?RedirectResponse
    {
        $token = $request->session()->get('api_token');
        if (! is_string($token) || $token === '') {
            return redirect()->route('admin.index');
        }

        if ($this->tokenExpiresSoon($token) && ! $this->renewToken($request)) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return to_route('admin.index')->withErrors(['login' => 'Your session expired. Please sign in again.']);
        }

        return null;
    }

    private function revokeBackendSession(Request $request): void
    {
        $token = $request->session()->get('api_token');
        if (! is_string($token) || $token === '') {
            return;
        }

        try {
            $response = $this->backendRequest()
                ->withHeaders(['X-Auth-Token' => $token])
                ->patch('/logout');

            if ($response->failed() && $response->status() !== 460) {
                Log::warning('Backend logout failed', ['status' => $response->status()]);
            }
        } catch (\Throwable $exception) {
            // Local logout must still succeed if the backend is temporarily unavailable.
            Log::warning('Backend logout request failed', ['exception' => $exception->getMessage()]);
        }
    }

    private function backendRequest(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.eepy.url'), '/'))
            ->acceptJson()
            ->withUserAgent("eepy.page-admin-panel/1.0")
            ->timeout(20);
    }

    private function refreshTokenFromResponse(Response $response): ?string
    {
        foreach ($response->headers() as $name => $values) {
            if (strcasecmp($name, 'Set-Cookie') !== 0) {
                continue;
            }

            foreach ((array) $values as $value) {
                $cookie = SetCookie::fromString($value);
                if ($cookie->getName() === $this->backendCookieName() && $cookie->getValue() !== null) {
                    return $cookie->getValue();
                }
            }
        }

        return null;
    }

    private function tokenExpiresSoon(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return true;
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        $expiresAt = is_array($payload) ? ($payload['exp'] ?? null) : null;

        return ! is_int($expiresAt) || $expiresAt <= time() + self::TOKEN_RENEWAL_LEEWAY_SECONDS;
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder !== 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
    }

    private function manualLogin(Request $request, string $userId): void
    {
        $tokens = $this->api($request, 'POST', '/admin/user/manual-login', body: ['user_id' => $userId]);
        $accessToken = $tokens['access_token'] ?? null;
        if (! is_string($accessToken)) {
            abort(502, 'The backend did not return a manual login token.');
        }

        $request->session()->put('manual_login', [
            'user_id' => $userId,
            'refresh_token' => $tokens['refresh_token'],
            'access_token' => $accessToken,
            'snippet' => "document.cookie = 'auth-token=' + encodeURIComponent(".json_encode($accessToken).") + '; Path=/; Secure; SameSite=Strict';\ndocument.cookie = 'logged-in=yes; Path=/';\nlocalStorage.setItem('logged-in', 'y');\nlocation.reload();",
        ]);
    }

    private function desync(Request $request): void
    {
        $request->session()->flash('desync', $this->api($request, 'GET', '/admin/dns/desync'));
    }

    private function updatePermissions(Request $request, string $userId, array $permissions, bool $sendEmail = false): void
    {
        uksort($permissions, static fn (string $left, string $right): int => match (true) {
            $left === 'enabled' => 1,
            $right === 'enabled' => -1,
            default => 0,
        });

        foreach ($permissions as $permission => $value) {
            $this->api($request, 'PATCH', '/admin/user/permission', query: [
                'id' => $userId,
                'permission' => $permission,
                'value' => $this->castPermissionValue($value),
                'send_email' => $sendEmail,
            ]);
        }
    }

    private function refreshLoadedUsers(Request $request): void
    {
        $users = $request->session()->get('users', []);
        $refreshedUsers = [];

        foreach ($users as $user) {
            $userId = $user['id'] ?? null;
            if (! is_string($userId) || $userId === '') {
                continue;
            }

            try {
                $refreshedUsers[] = $this->api($request, 'GET', '/admin/user/get/id', query: ['id' => $userId]);
            } catch (HttpExceptionInterface $exception) {
                if ($exception->getStatusCode() !== 404) {
                    throw $exception;
                }
            }
        }

        $request->session()->put('users', $refreshedUsers);
    }

    private function backendCookieName(): string
    {
        return 'refresh-token';
    }

    private function viewData(Request $request, string $page): array
    {
        return [
            'page' => $page,
            'users' => $request->session()->get('users', []),
            'tokenSet' => $request->session()->has('api_token'),
            'apiUrl' => config('services.eepy.url'),
            'turnstileSiteKey' => config('services.eepy.turnstile_site_key'),
            'tlds' => self::TLDs,
        ];
    }

    private function backendCookieDomain(): string
    {
        $host = parse_url(config('services.eepy.url'), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : 'api.eepy.page';
    }

    private function castValue(string $value): string|int|float|bool
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            default => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
        };
    }

    private function castPermissionValue(string $value): string|int|float
    {
        return match (strtolower($value)) {
            'true' => 'true',
            'false' => 'false',
            default => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
        };
    }

    private function formatApiError(
        Response $response,
    ): string {
        $json = $response->json();
        $jsonText = is_array($json) || is_object($json)
            ? json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : null;

        return "Backend API request failed\n"
            ."HTTP status: {$response->status()}\n"
            ."Raw response:\n{$response->body()}\n"
            .($jsonText !== false && $jsonText !== null ? "Decoded JSON:\n{$jsonText}" : 'Decoded JSON: unavailable');
    }
}
