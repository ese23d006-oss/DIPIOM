<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ачаалал хуваарилагчийн /up шалгалтыг HTTPS руу чиглүүлэхгүй.
        if (config('lms.force_https') && ! $request->isSecure() && ! $request->is('up')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
