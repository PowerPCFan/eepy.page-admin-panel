<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AdminAuthenticationTest extends TestCase
{
    public function test_successful_login_keeps_the_backend_token_in_the_server_session(): void
    {
        Http::fake([
            'https://api.eepy.page/login' => Http::response(['auth-token' => 'server-only-token'], 200, [
                'Set-Cookie' => 'refresh-token=server-only-refresh-token; Path=/refresh; HttpOnly; Secure; SameSite=None',
            ]),
            'https://api.eepy.page/admin/user/can-access' => Http::response([], 200),
        ]);

        $response = $this->post('/login', [
            'username' => 'admin-user',
            'password' => 'correct-password',
            'captcha' => 'turnstile-token',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('api_token', 'server-only-token');
        $response->assertSessionHas('api_refresh_token', 'server-only-refresh-token');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.eepy.page/login'
            && $request['username_hash'] === hash('sha256', 'admin-user')
            && $request['password'] === 'correct-password'
            && $request->header('X-Captcha-Code') === ['turnstile-token']
            && $request->header('User-Agent') === ['eepy.page-admin-panel/1.0']);
    }

    public function test_rejected_login_does_not_create_an_authenticated_session(): void
    {
        Http::fake([
            'https://api.eepy.page/login' => Http::response(['detail' => 'Invalid password'], 401),
        ]);

        $response = $this->post('/login', [
            'username' => 'admin-user',
            'password' => 'wrong-password',
            'captcha' => 'turnstile-token',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('login');
        $response->assertSessionMissing('api_token');
    }

    public function test_an_expiring_access_token_is_refreshed_before_rendering_a_protected_page(): void
    {
        Http::fake([
            'https://api.eepy.page/refresh' => Http::response(['auth-token' => 'new-access-token'], 200, [
                'Set-Cookie' => 'refresh-token=new-refresh-token; Path=/refresh; HttpOnly; Secure; SameSite=Lax',
            ]),
        ]);

        $response = $this->withSession([
            'api_token' => $this->tokenExpiringAt(time() + 30),
            'api_refresh_token' => 'old-refresh-token',
        ])->get('/manage/utilities');

        $response->assertOk();
        $response->assertSessionHas('api_token', 'new-access-token');
        $response->assertSessionHas('api_refresh_token', 'new-refresh-token');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.eepy.page/refresh'
            && $request->header('User-Agent') === ['eepy.page-admin-panel/1.0']);
    }

    public function test_logout_revokes_the_backend_session_before_clearing_the_panel_session(): void
    {
        Http::fake([
            'https://api.eepy.page/logout' => Http::response([], 200),
        ]);

        $response = $this->withSession(['api_token' => 'active-access-token'])->post('/logout');

        $response->assertRedirect('/');
        $response->assertSessionMissing('api_token');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.eepy.page/logout'
            && $request->method() === 'PATCH'
            && $request->header('X-Auth-Token') === ['active-access-token']);
    }

    private function tokenExpiringAt(int $expiresAt): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode(['exp' => $expiresAt], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

        return 'header.'.$payload.'.signature';
    }
}
