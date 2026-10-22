<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The site is served through a Cloudflare tunnel that ends https, so forwarded headers must be honoured.
 */
class ProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_forwarded_https_makes_urls_secure(): void
    {
        $this->get('/login', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'sa.example.com'])
            ->assertOk()
            ->assertSee('https://sa.example.com/', false)
            ->assertDontSee('http://sa.example.com', false);
    }

    public function test_plain_requests_stay_http(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('https://localhost', false);
    }
}
