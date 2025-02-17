<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureDocsAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('PHP_AUTH_USER') || !$request->hasHeader('PHP_AUTH_PW')) {
            return response()->make('', 401, [
                'WWW-Authenticate' => 'Basic realm="API Documentation"'
            ]);
        }

        if ($request->header('PHP_AUTH_PW') !== env('SCRAMBLE_DOCS_PASSWORD')) {
            return response()->make('Invalid credentials', 401, [
                'WWW-Authenticate' => 'Basic realm="API Documentation"'
            ]);
        }

        return $next($request);
    }
}
