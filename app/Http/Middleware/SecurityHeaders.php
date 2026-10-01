<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras HTTP de seguridad básicas para todas las respuestas web
 * y de API.
 *
 * No incluye Content-Security-Policy a propósito: las vistas usan
 * scripts inline (Livewire/Alpine, Google Maps, carrusel de la
 * landing) y una CSP mal calibrada rompería páginas. Agregarla
 * requiere revisar cada vista primero.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;

        // Evita que el sitio se cargue dentro de un <iframe> ajeno
        // (clickjacking sobre botones como "Confirmar entrega").
        $headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $headers->set('X-Content-Type-Options', 'nosniff', false);
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);

        // Cámara (escáner QR) y ubicación (localizador de agencias,
        // ruta del repartidor) solo desde el propio sitio.
        $headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()', false);

        // HSTS solo tiene efecto (y solo debe enviarse) sobre HTTPS.
        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        return $response;
    }
}
