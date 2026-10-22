<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The site is served through a Cloudflare tunnel that ends https. Created URLs come from APP_URL, so
 * a forwarded host sent by a client cannot change them (it would redirect password reset links).
 */
class ProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_urls_use_app_url_and_ignore_a_forwarded_host(): void
    {
        config(['app.url' => 'https://sa.example.com']);

        $this->get('/login', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'evil.example.net'])
            ->assertOk()
            ->assertSee('https://sa.example.com/', false)
            ->assertDontSee('evil.example.net', false)
            ->assertDontSee('http://sa.example.com', false);
    }

    public function test_plain_requests_stay_http(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('https://localhost', false);
    }
}
