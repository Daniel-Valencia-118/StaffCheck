<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class CabecerasSeguridad
{
    private const MOSAICOS = 'https://tile.openstreetmap.org https://*.tile.openstreetmap.org';

    private const TURNSTILE = 'https://challenges.cloudflare.com';

    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), payment=(), geolocation=(self)');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        if (! Vite::isRunningHot()) {
            $aplicar = rescue(fn () => (bool) Configuracion::valor('csp_aplicar'), false, report: false);
            $cabecera = $aplicar ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';
            $response->headers->set($cabecera, self::politica($nonce));
        }

        return $response;
    }

    public static function politica(string $nonce): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' ".self::TURNSTILE,
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: ".self::MOSAICOS,
            "font-src 'self' data:",
            "connect-src 'self' ".self::MOSAICOS,
            'frame-src '.self::TURNSTILE,
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
