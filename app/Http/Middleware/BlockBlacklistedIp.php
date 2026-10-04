<?php

namespace App\Http\Middleware;

use App\Services\BlacklistService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBlacklistedIp
{
    public function __construct(private BlacklistService $blacklist) {}

    /**
     * Tolak request API yang berasal dari alamat IP blacklist.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->blacklist->isBlacklistedIp($request->ip())) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        return $next($request);
    }
}
