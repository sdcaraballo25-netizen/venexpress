<?php

namespace App\Mail;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

/**
 * Envía los correos por la API HTTP de Gmail (puerto 443).
 *
 * En el plan gratis de Render los puertos SMTP (25, 465 y 587) están
 * bloqueados, así que Gmail por SMTP no funciona allí. La API de Gmail
 * va por HTTPS y sí funciona. Se autentica con OAuth: un refresh token
 * de la cuenta remitente con el permiso gmail.send (ver
 * docs/DESPLIEGUE_RENDER.md, "Correo").
 */
class GmailApiTransport extends AbstractTransport
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SEND_URL = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $refreshToken,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        Http::withToken($this->accessToken())
            ->timeout(30)
            ->post(self::SEND_URL, [
                'raw' => rtrim(strtr(base64_encode($message->toString()), '+/', '-_'), '='),
            ])
            ->throw();
    }

    /**
     * Los access token de Google duran 1 hora; se reutilizan 50 min.
     */
    private function accessToken(): string
    {
        return Cache::remember('gmail-api-access-token:' . md5($this->clientId), now()->addMinutes(50), function () {
            return Http::asForm()
                ->timeout(30)
                ->post(self::TOKEN_URL, [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                ])
                ->throw()
                ->json('access_token');
        });
    }

    public function __toString(): string
    {
        return 'gmail+api://default';
    }
}
