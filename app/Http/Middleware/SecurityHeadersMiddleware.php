<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $viteHttp = app()->isLocal() ? ' http://localhost:5173 http://127.0.0.1:5173' : '';
        $viteWs = app()->isLocal() ? ' ws://localhost:5173 ws://127.0.0.1:5173' : '';

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'{$viteHttp}; style-src 'self' 'unsafe-inline' https://cdn-uicons.flaticon.com{$viteHttp}; font-src 'self' data: https://cdn-uicons.flaticon.com{$viteHttp}; img-src 'self' data: blob:; connect-src 'self'{$viteHttp}{$viteWs}; frame-src 'self'; object-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
