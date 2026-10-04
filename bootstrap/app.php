<?php

use App\Console\Commands\HitungBungaTabungan;
use App\Http\Middleware\BlockBlacklistedIp;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // App\Http\Middleware\TrustProxies.php
        $middleware->redirectGuestsTo(fn () => route('login.modern'));

        // Blokir alamat IP blacklist sebelum middleware lain di grup API berjalan.
        $middleware->api(prepend: [
            BlockBlacklistedIp::class,
        ]);

        $middleware->trustProxies(
            '*',
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Biarkan Laravel menangani validasi (422) dengan format standar
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $e->errors(),
                    ], 422);
                }

                // Token tidak valid / tidak ada -> 401, bukan 500
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthenticated',
                    ], 401);
                }

                // Rate limit terlampaui -> 429 dengan header Retry-After
                if ($e instanceof ThrottleRequestsException) {
                    $headers = $e->getHeaders();
                    $retryAfter = (int) ($headers['Retry-After'] ?? 0);

                    return response()->json([
                        'status' => false,
                        'message' => $retryAfter > 0
                            ? "Terlalu banyak permintaan. Coba lagi dalam {$retryAfter} detik."
                            : 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                        'retry_after' => $retryAfter,
                    ], 429, $headers);
                }

                // Tentukan status code dengan fallback
                $statusCode = $e instanceof HttpException
                    ? $e->getStatusCode()
                    : 500;

                // Struktur response JSON
                $response = [
                    'status' => false,
                    'message' => 'Terjadi kesalahan pada server',
                ];

                // Tambahkan informasi error jika mode debug aktif
                if (config('app.debug')) {
                    $response['error'] = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                }

                return response()->json($response, $statusCode);
            }
        });

    })
    ->withCommands([
        HitungBungaTabungan::class,
    ])
    ->create();
