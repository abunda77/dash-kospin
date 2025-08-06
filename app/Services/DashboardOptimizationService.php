<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardOptimizationService
{
    /**
     * Optimize query by selecting only necessary columns
     */
    public function optimizeLoanQuery($query)
    {
        return $query->select([
            'id',
            'kode_pinjaman',
            'profile_id',
            'produk_pinjaman_id',
            'jumlah_pinjaman',
            'tanggal_pinjaman',
            'tanggal_jatuh_tempo',
            'status_pinjaman',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * Optimize eager loading relationships
     */
    public function optimizeEagerLoading()
    {
        return [
            'profile:id,user_id,nomor_anggota',
            'profile.user:id,name,email',
            'produkPinjaman:id,nama_produk',
            'transaksiPinjaman' => function ($query) {
                $query->select('id', 'pinjaman_id', 'angsuran_ke', 'jumlah_pembayaran', 'tanggal_pembayaran')
                      ->latest('tanggal_pembayaran')
                      ->limit(5);
            }
        ];
    }

    /**
     * Cache query results with tags
     */
    public function cacheQuery($key, $callback, $ttl = 300)
    {
        return Cache::tags(['dashboard', 'loans'])->remember($key, $ttl, $callback);
    }

    /**
     * Clear dashboard cache
     */
    public function clearDashboardCache()
    {
        Cache::tags(['dashboard', 'loans'])->flush();
    }

    /**
     * Get optimized query with caching
     */
    public function getOptimizedLoans($baseQuery, $filters = [])
    {
        $cacheKey = 'optimized_loans_' . md5(serialize($filters));
        
        return $this->cacheQuery($cacheKey, function () use ($baseQuery, $filters) {
            $query = $this->optimizeLoanQuery($baseQuery);
            $query->with($this->optimizeEagerLoading());
            
            if (!empty($filters['product_id'])) {
                $query->where('produk_pinjaman_id', $filters['product_id']);
            }
            
            if (!empty($filters['date_range'])) {
                $query->whereBetween('tanggal_pinjaman', [
                    $filters['date_range']['start'],
                    $filters['date_range']['end']
                ]);
            }
            
            return $query;
        });
    }
}