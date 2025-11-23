<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        
        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block', false);
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        
        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()', false);
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https://api.binderbyte.com; " .
               "frame-ancestors 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
        $response->headers->set('Content-Security-Policy', $csp, false);
        
        // HSTS (only in production with HTTPS)
        if (config('app.env') === 'production' && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload', false);
        }

        return $response;
    }
}

