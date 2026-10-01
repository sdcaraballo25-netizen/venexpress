<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_responses_include_basic_security_headers(): void
    {
        $this->get(route('tracking.index'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_only_over_https(): void
    {
        $this->get('https://localhost'.route('tracking.index', absolute: false))
            ->assertHeader('Strict-Transport-Security');
    }

    public function test_api_responses_include_the_headers_too(): void
    {
        $this->postJson('/api/driver/login', [])
            ->assertStatus(422)
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
