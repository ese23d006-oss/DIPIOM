<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * style-src 'unsafe-inline' нь style аттрибутад (текстийн хэмжээ, графикийн өндөр) хэрэгтэй.
     * Скриптийг зөвхөн өөрийн серверээс зөвшөөрнө, тиймээс XSS-ийн гол замыг хаана.
     * media-src blob: нь видеоны уртыг хөтөч дээр уншихад хэрэгтэй.
     */
    private const CSP = "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        ."img-src 'self' data:; font-src 'self'; media-src 'self' blob:; connect-src 'self'; "
        ."object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // `npm run dev` горимд Vite өөр портоос ачаалдаг.
        if (! Vite::isRunningHot()) {
            $response->headers->set('Content-Security-Policy', self::CSP);
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
