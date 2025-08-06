<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceHelper
{
    /**
     * Monitor query performance for debugging
     */
    public static function monitorQueries(callable $callback, string $operation = 'operation')
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Enable query logging
        DB::enableQueryLog();
        
        try {
            $result = $callback();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            $queries = DB::getQueryLog();
            
            // Log performance metrics
            Log::info("Performance Monitor - {$operation}", [
                'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
                'memory_used' => self::formatBytes($endMemory - $startMemory),
                'peak_memory' => self::formatBytes(memory_get_peak_usage(true)),
                'query_count' => count($queries),
                'slow_queries' => self::getSlowQueries($queries),
            ]);
            
            return $result;
            
        } finally {
            DB::disableQueryLog();
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Identify slow queries (> 100ms)
     */
    private static function getSlowQueries(array $queries): array
    {
        return array_filter($queries, function ($query) {
            return $query['time'] > 100; // 100ms threshold
        });
    }
    
    /**
     * Cache expensive operations with TTL
     */
    public static function cache(string $key, callable $callback, int $ttlMinutes = 5)
    {
        return cache()->remember($key, now()->addMinutes($ttlMinutes), $callback);
    }
    
    /**
     * Batch process large datasets to prevent memory issues
     */
    public static function batchProcess($query, callable $callback, int $batchSize = 1000)
    {
        $processed = 0;
        
        $query->chunk($batchSize, function ($items) use ($callback, &$processed) {
            $callback($items);
            $processed += $items->count();
            
            // Log progress for large operations
            if ($processed % 5000 === 0) {
                Log::info("Batch processing progress: {$processed} items processed");
            }
        });
        
        return $processed;
    }
}
