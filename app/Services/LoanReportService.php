<?php

namespace App\Services;

use App\Models\Pinjaman;
use App\Models\TransaksiPinjaman;
use App\Models\ProdukPinjaman;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LoanReportService
{
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING = 'pending';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    public function __construct(
        private ?string $productFilter = null,
        private ?array $dateRange = null
    ) {}

    public function setProductFilter(?string $productFilter): self
    {
        $this->productFilter = $productFilter;
        return $this;
    }

    public function setDateRange(array $dateRange): self
    {
        $this->dateRange = $dateRange;
        return $this;
    }

    public function getBaseQuery(): Builder
    {
        return Pinjaman::query()
            ->with(['profile.user', 'produkPinjaman', 'transaksiPinjaman'])
            ->when($this->productFilter, fn($query) => $query->where('produk_pinjaman_id', $this->productFilter));
    }

    public function getApprovedLoansQuery(): Builder
    {
        return $this->getBaseQuery()
            ->where('status_pinjaman', self::STATUS_APPROVED)
            ->when($this->dateRange, function ($query) {
                $query->whereDate('tanggal_pinjaman', '>=', $this->dateRange['start'])
                      ->whereDate('tanggal_pinjaman', '<=', $this->dateRange['end']);
            });
    }

    public function getLoanStats(): array
    {
        $stats = $this->getApprovedLoansQuery()
            ->selectRaw('
                COUNT(*) as active_loans,
                SUM(jumlah_pinjaman) as total_loan_amount,
                AVG(jumlah_pinjaman) as avg_loan_amount
            ')
            ->first();

        $overdueLoans = $this->getApprovedLoansQuery()
            ->where('tanggal_jatuh_tempo', '<', Carbon::today())
            ->count();

        $paymentStats = TransaksiPinjaman::query()
            ->whereHas('pinjaman', function ($q) {
                $q->where('status_pinjaman', self::STATUS_APPROVED)
                  ->when($this->productFilter, fn($query) => $query->where('produk_pinjaman_id', $this->productFilter));
            })
            ->when($this->dateRange, function ($query) {
                $query->whereDate('tanggal_pembayaran', '>=', $this->dateRange['start'])
                      ->whereDate('tanggal_pembayaran', '<=', $this->dateRange['end']);
            })
            ->selectRaw('
                COUNT(*) as payment_count,
                SUM(total_pembayaran) as total_payments
            ')
            ->first();

        return [
            'active_loans' => $stats->active_loans ?? 0,
            'total_loan_amount' => $stats->total_loan_amount ?? 0,
            'avg_loan_amount' => $stats->avg_loan_amount ?? 0,
            'overdue_loans' => $overdueLoans,
            'total_payments' => $paymentStats->total_payments ?? 0,
            'payment_count' => $paymentStats->payment_count ?? 0,
        ];
    }

    public function getProductDistribution(): array
    {
        $data = DB::table('pinjamans')
            ->join('produk_pinjamans', 'pinjamans.produk_pinjaman_id', '=', 'produk_pinjamans.id')
            ->where('pinjamans.status_pinjaman', self::STATUS_APPROVED)
            ->when($this->dateRange, function ($query) {
                $query->whereDate('pinjamans.tanggal_pinjaman', '>=', $this->dateRange['start'])
                      ->whereDate('pinjamans.tanggal_pinjaman', '<=', $this->dateRange['end']);
            })
            ->when($this->productFilter, function ($query) {
                $query->where('pinjamans.produk_pinjaman_id', $this->productFilter);
            })
            ->select('produk_pinjamans.nama_produk', DB::raw('COUNT(*) as count'), DB::raw('SUM(pinjamans.jumlah_pinjaman) as total_amount'))
            ->groupBy('produk_pinjamans.nama_produk')
            ->get();

        return [
            'labels' => $data->pluck('nama_produk')->toArray(),
            'data' => $data->pluck('count')->toArray(),
            'amounts' => $data->pluck('total_amount')->toArray(),
        ];
    }

    public function getMonthlyLoanTrends(): array
    {
        $months = [];
        $loanCounts = [];
        $loanAmounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i);
            $months[] = $month->format('M Y');

            $query = Pinjaman::query()
                ->where('status_pinjaman', self::STATUS_APPROVED)
                ->whereYear('tanggal_pinjaman', $month->year)
                ->whereMonth('tanggal_pinjaman', $month->month)
                ->when($this->productFilter, fn($query) => $query->where('produk_pinjaman_id', $this->productFilter));

            $loanCounts[] = $query->count();
            $loanAmounts[] = $query->sum('jumlah_pinjaman');
        }

        return [
            'months' => $months,
            'counts' => $loanCounts,
            'amounts' => $loanAmounts,
        ];
    }

    public function getPaymentTrends(): array
    {
        $months = [];
        $paymentCounts = [];
        $paymentAmounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::today()->subMonths($i);
            $months[] = $month->format('M Y');

            $query = TransaksiPinjaman::query()
                ->whereYear('tanggal_pembayaran', $month->year)
                ->whereMonth('tanggal_pembayaran', $month->month)
                ->whereHas('pinjaman', function ($q) {
                    $q->where('status_pinjaman', self::STATUS_APPROVED)
                      ->when($this->productFilter, fn($query) => $query->where('produk_pinjaman_id', $this->productFilter));
                });

            $paymentCounts[] = $query->count();
            $paymentAmounts[] = $query->sum('total_pembayaran');
        }

        return [
            'months' => $months,
            'counts' => $paymentCounts,
            'amounts' => $paymentAmounts,
        ];
    }

    public function getDateRange(string $period, array $customRange = []): array
    {
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                return [
                    'start' => $today->copy(),
                    'end' => $today->copy()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $today->copy()->startOfWeek(),
                    'end' => $today->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => $today->copy()->startOfMonth(),
                    'end' => $today->copy()->endOfMonth(),
                ];
            case 'year':
                return [
                    'start' => $today->copy()->startOfYear(),
                    'end' => $today->copy()->endOfYear(),
                ];
            case 'custom':
                return [
                    'start' => isset($customRange['start_date']) ? Carbon::parse($customRange['start_date']) : $today->copy()->subDays(30),
                    'end' => isset($customRange['end_date']) ? Carbon::parse($customRange['end_date']) : $today->copy(),
                ];
            default:
                return [
                    'start' => $today->copy()->startOfMonth(),
                    'end' => $today->copy()->endOfMonth(),
                ];
        }
    }
}