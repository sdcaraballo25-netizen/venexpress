<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * MAIL_MAILER=gmail envía por la API HTTPS de Gmail, porque Render
 * gratis bloquea los puertos SMTP.
 */
class GmailApiTransportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'gmail',
            'mail.mailers.gmail.client_id' => 'client-id',
            'mail.mailers.gmail.client_secret' => 'client-secret',
            'mail.mailers.gmail.refresh_token' => 'refresh-token',
            'mail.from.address' => 'remitente@gmail.com',
        ]);
    }

    public function test_sends_the_message_through_the_gmail_api(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-123', 'expires_in' => 3599]),
            'gmail.googleapis.com/*' => Http::response(['id' => 'abc']),
        ]);

        Mail::raw('Hola desde Venexpress', fn ($m) => $m->to('cliente@example.com')->subject('Prueba'));
        Mail::raw('Segundo correo', fn ($m) => $m->to('otro@example.com')->subject('Prueba 2'));

        Http::assertSent(fn (Request $request) => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['refresh_token'] === 'refresh-token'
            && $request['grant_type'] === 'refresh_token');

        Http::assertSent(function (Request $request) {
            if ($request->url() !== 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send') {
                return false;
            }

            $mime = base64_decode(strtr($request['raw'], '-_', '+/'));

            return $request->hasHeader('Authorization', 'Bearer access-123')
                && str_contains($mime, 'To: cliente@example.com')
                && str_contains($mime, 'Subject: Prueba')
                && str_contains($mime, 'Hola desde Venexpress');
        });

        // El access token se reutiliza: una sola llamada a /token.
        Http::assertSentCount(3);
    }

    public function test_a_rejected_send_is_reported_as_an_error(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-123']),
            'gmail.googleapis.com/*' => Http::response(['error' => 'forbidden'], 403),
        ]);

        $this->expectException(\Throwable::class);

        Mail::raw('Hola', fn ($m) => $m->to('cliente@example.com')->subject('Prueba'));
    }
}
