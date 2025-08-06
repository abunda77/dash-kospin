<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizeDashboardPerformance
{
    public function handle($request, Closure $next)
    {
        // Disable query log for dashboard requests
        if ($request->is('admin/laporan-pinjaman*')) {
            DB::disableQueryLog();
            
            // Set cache headers for static assets
            $response = $next($request);
            
            $response->headers->set('Cache-Control', 'public, max-age=300');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            
            return $response;
        }

        return $next($request);
    }
}