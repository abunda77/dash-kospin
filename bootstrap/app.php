<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // App\Http\Middleware\TrustProxies.php
        $middleware->trustProxies(
            '*',
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Tentukan status code dengan fallback
                $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                    ? $e->getStatusCode()
                    : 500;

                // Struktur response JSON
                $response = [
                    'status' => false,
                    'message' => 'Terjadi kesalahan pada server'
                ];

                // Tambahkan informasi error jika mode debug aktif
                if (config('app.debug')) {
                    $response['error'] = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ];
                }

                return response()->json($response, $statusCode);
            }
        });

    })
    ->withCommands([
        App\Console\Commands\HitungBungaTabungan::class,
    ])
    ->create();
