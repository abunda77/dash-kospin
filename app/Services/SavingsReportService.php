<?php

namespace App\Services;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\ProdukTabungan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SavingsReportService
{
    protected $productFilter;
    protected $dateRange;

    public function __construct($productFilter = null, array $dateRange = [])
    {
        $this->productFilter = $productFilter;
        $this->dateRange = $dateRange;
    }

    public function getDateRange(string $period, array $customRange = []): array
    {
        return match ($period) {
            'today' => [
                'start_date' => now()->startOfDay(),
                'end_date' => now()->endOfDay(),
            ],
            'week' => [
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
            ],
            'month' => [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
            ],
            'year' => [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ],
            'custom' => [
                'start_date' => $customRange['start_date'] ?? now()->startOfMonth(),
                'end_date' => $customRange['end_date'] ?? now(),
            ],
            default => [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ],
        };
    }

    public function getActiveSavingsQuery(): Builder
    {
        $query = Tabungan::query()
            ->with(['profile.user', 'produkTabungan', 'transaksi'])
            ->where('status_rekening', 'aktif');
            
        if ($this->productFilter) {
            $query->where('produk_tabungan', $this->productFilter);
        }
        
        if (!empty($this->dateRange['start_date']) && !empty($this->dateRange['end_date'])) {
            $query->whereBetween('tanggal_buka_rekening', [
                $this->dateRange['start_date'],
                $this->dateRange['end_date']
            ]);
        }
        
        return $query;
    }

    public function getSavingsStats(): array
    {
        $tabunganData = $this->getActiveSavingsQuery()->get();
        
        // Transaction stats for the same period
        $transaksiQuery = TransaksiTabungan::query()
            ->whereHas('tabungan', function ($q) {
                $q->where('status_rekening', 'aktif');
                if ($this->productFilter) {
                    $q->where('produk_tabungan', $this->productFilter);
                }
            });
            
        if (!empty($this->dateRange['start_date']) && !empty($this->dateRange['end_date'])) {
            $transaksiQuery->whereBetween('tanggal_transaksi', [
                $this->dateRange['start_date'],
                $this->dateRange['end_date']
            ]);
        }
        
        $transaksiData = $transaksiQuery->get();
        
        return [
            'total_accounts' => $tabunganData->count(),
            'total_balance' => $tabunganData->sum('saldo'),
            'avg_balance' => $tabunganData->avg('saldo') ?? 0,
            'total_deposits' => $transaksiData->where('jenis_transaksi', TransaksiTabungan::JENIS_SETORAN)->sum('jumlah'),
            'total_withdrawals' => $transaksiData->where('jenis_transaksi', TransaksiTabungan::JENIS_PENARIKAN)->sum('jumlah'),
            'transaction_count' => $transaksiData->count(),
            'deposit_count' => $transaksiData->where('jenis_transaksi', TransaksiTabungan::JENIS_SETORAN)->count(),
            'withdrawal_count' => $transaksiData->where('jenis_transaksi', TransaksiTabungan::JENIS_PENARIKAN)->count(),
        ];
    }

    public function getProductDistribution(): array
    {
        $query = $this->getActiveSavingsQuery();
        
        return $query->get()
            ->groupBy('produkTabungan.nama_produk')
            ->map(function ($group, $productName) {
                return [
                    'product' => $productName ?? 'Unknown',
                    'count' => $group->count(),
                    'total_balance' => $group->sum('saldo'),
                    'avg_balance' => $group->avg('saldo'),
                ];
            })
            ->values()
            ->toArray();
    }

    public function getMonthlySavingsTrends(): array
    {
        $transaksiQuery = TransaksiTabungan::query()
            ->whereHas('tabungan', function ($q) {
                $q->where('status_rekening', 'aktif');
                if ($this->productFilter) {
                    $q->where('produk_tabungan', $this->productFilter);
                }
            });
            
        if (!empty($this->dateRange['start_date']) && !empty($this->dateRange['end_date'])) {
            $transaksiQuery->whereBetween('tanggal_transaksi', [
                $this->dateRange['start_date'],
                $this->dateRange['end_date']
            ]);
        }
        
        return $transaksiQuery->get()
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->tanggal_transaksi)->format('Y-m');
            })
            ->map(function ($group, $month) {
                $deposits = $group->where('jenis_transaksi', TransaksiTabungan::JENIS_SETORAN)->sum('jumlah');
                $withdrawals = $group->where('jenis_transaksi', TransaksiTabungan::JENIS_PENARIKAN)->sum('jumlah');
                
                return [
                    'month' => $month,
                    'deposits' => $deposits,
                    'withdrawals' => $withdrawals,
                    'net_flow' => $deposits - $withdrawals,
                    'transaction_count' => $group->count(),
                ];
            })
            ->sortBy('month')
            ->values()
            ->toArray();
    }

    public function getTransactionTrends(): array
    {
        return $this->getMonthlySavingsTrends();
    }

    public function getTopSavers(int $limit = 10): array
    {
        Log::info('SavingsReportService@getTopSavers called', [
            'limit' => $limit,
            'productFilter' => $this->productFilter,
        ]);

        try {
            // Khusus Top Savers: JANGAN batasi dengan tanggal_buka_rekening,
            // karena tujuan adalah saldo terbesar saat ini pada rekening aktif.
            $query = Tabungan::query()
                ->with(['profile.user', 'produkTabungan'])
                ->where('status_rekening', 'aktif');

            if ($this->productFilter) {
                $query->where('produk_tabungan', $this->productFilter);
            }

            // Log preview query (SQL + bindings)
            Log::debug('SavingsReportService@getTopSavers query preview', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'orders' => [['column' => 'saldo', 'direction' => 'desc']],
                'limit' => $limit,
            ]);

            $results = $query
                ->orderBy('saldo', 'desc')
                ->limit($limit)
                ->get();

            Log::info('SavingsReportService@getTopSavers fetched results', [
                'count' => $results->count(),
                'top_sample' => $results->take(3)->map(function ($t) {
                    return [
                        'no_tabungan' => $t->no_tabungan,
                        'saldo' => $t->saldo,
                        'produk' => $t->produkTabungan?->nama_produk,
                        'nama' => $t->profile?->user?->name,
                    ];
                }),
            ]);

            $payload = $results->map(function ($tabungan) {
                return [
                    'name' => $tabungan->profile?->user?->name ?? 'Unknown',
                    'account_number' => $tabungan->no_tabungan,
                    'product' => $tabungan->produkTabungan?->nama_produk ?? 'Unknown',
                    'balance' => $tabungan->saldo,
                    'account_age_days' => Carbon::parse($tabungan->tanggal_buka_rekening)->diffInDays(now()),
                ];
            })->toArray();

            Log::debug('SavingsReportService@getTopSavers mapped payload preview', [
                'payload_count' => count($payload),
                'first_item' => $payload[0] ?? null,
            ]);

            return $payload;
        } catch (\Throwable $e) {
            Log::error('SavingsReportService@getTopSavers failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => collect(explode("\n", $e->getTraceAsString()))->take(10),
            ]);
            throw $e;
        }
    }

    public function getAccountGrowthTrend(): array
    {
        $query = Tabungan::query()
            ->where('status_rekening', 'aktif');
            
        if ($this->productFilter) {
            $query->where('produk_tabungan', $this->productFilter);
        }
        
        if (!empty($this->dateRange['start_date']) && !empty($this->dateRange['end_date'])) {
            $query->whereBetween('tanggal_buka_rekening', [
                $this->dateRange['start_date'],
                $this->dateRange['end_date']
            ]);
        }
        
        return $query->get()
            ->groupBy(function ($tabungan) {
                return Carbon::parse($tabungan->tanggal_buka_rekening)->format('Y-m');
            })
            ->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'new_accounts' => $group->count(),
                    'total_initial_balance' => $group->sum('saldo'),
                ];
            })
            ->sortBy('month')
            ->values()
            ->toArray();
    }
}