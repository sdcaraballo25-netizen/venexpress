<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Envenenamiento de enlaces por cabecera Host: con TRUSTED_PROXIES="*"
 * (Render) un visitante podía mandar X-Forwarded-Host y Laravel armaba
 * los enlaces —incluido el del correo de "olvidé mi contraseña"— con su
 * dominio, robando el token de restablecimiento.
 */
class HostHeaderInjectionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TrustProxies::flushState();
        URL::forceRootUrl(null);
        URL::forceScheme(null);

        parent::tearDown();
    }

    private function trustAllProxiesLikeRender(): void
    {
        config(['app.trusted_proxies' => '*']);

        $method = new \ReflectionMethod(AppServiceProvider::class, 'configureTrustedProxies');
        $method->invoke(new AppServiceProvider($this->app));
    }

    public function test_forwarded_host_header_is_ignored_even_with_all_proxies_trusted(): void
    {
        $this->trustAllProxiesLikeRender();

        $html = $this->withHeaders(['X-Forwarded-Host' => 'evil.example', 'X-Forwarded-Proto' => 'https'])
            ->get('/')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('evil.example', $html);
    }

    public function test_forwarded_proto_is_still_honoured_for_https_behind_the_proxy(): void
    {
        $this->trustAllProxiesLikeRender();

        $html = $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get('/')->getContent();

        $this->assertStringContainsString('https://localhost/', $html);
    }

    public function test_password_reset_link_never_uses_the_attackers_host(): void
    {
        $this->trustAllProxiesLikeRender();
        Notification::fake();

        $victim = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL]);

        $this->withHeaders(['X-Forwarded-Host' => 'evil.example', 'X-Forwarded-Proto' => 'https'])
            ->get('/forgot-password');

        $url = (new ResetPassword('token-de-prueba'))->toMail($victim)->actionUrl;

        $this->assertStringNotContainsString('evil.example', $url);
    }

    public function test_in_production_links_are_always_built_from_app_url(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://viayaa.example']);

        (new AppServiceProvider($this->app))->configureUrlGeneration();

        $this->assertSame('https://viayaa.example/rastreo', url('/rastreo'));
        $this->assertSame('https://viayaa.example/rastreo', route('tracking.index'));
    }
}
