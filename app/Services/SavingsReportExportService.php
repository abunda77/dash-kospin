<?php

namespace App\Services;

use App\Models\TransaksiTabungan;
use App\Models\Tabungan;
use App\Models\ProdukTabungan;
use App\Helpers\PdfHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SavingsReportExportService
{
    protected SavingsReportService $savingsReportService;

    /**
     * Optional progress callback. Signature: function(int $processed, int $total, float $percent)
     * Dapat di-set dari controller / job untuk update progress bar (misal simpan ke cache / broadcast event).
     */
    protected $progressCallback = null;

    public function __construct()
    {
        // Service will be created with specific filters when needed
    }

    /**
     * Set a progress callback used during chunked processing.
     */
    public function setProgressCallback(callable $callback): self
    {
        $this->progressCallback = $callback;
        return $this;
    }

    /**
     * Helper to invoke progress callback safely.
     */
    private function reportProgress(int $processed, int $total): void
    {
        if ($this->progressCallback && $total > 0) {
            $percent = round(($processed / $total) * 100, 2);
            try {
                call_user_func($this->progressCallback, $processed, $total, $percent);
            } catch (\Throwable $e) {
                Log::warning('Progress callback error: ' . $e->getMessage());
            }
        }
    }

    public function generateSavingsReportFile($productFilter, array $dateRange, string $filename): string
    {
        $service = new SavingsReportService($productFilter, $dateRange);
        $stats = $service->getSavingsStats();
        $savingsData = $service->getActiveSavingsQuery()->get();

        $html = $this->generateSavingsReportHtml($stats, $savingsData, $dateRange, $productFilter);

        return PdfHelper::generatePdf($html, $filename);
    }

    public function generateTransactionReportFile($productFilter, array $dateRange, string $filename): string
    {
        $service = new SavingsReportService($productFilter, $dateRange);

        // Get transaction data
        $transaksiQuery = TransaksiTabungan::query()
            ->with(['tabungan.profile.user', 'tabungan.produkTabungan', 'admin'])
            ->whereHas('tabungan', function ($q) use ($productFilter) {
                $q->where('status_rekening', 'aktif');
                if ($productFilter) {
                    $q->where('produk_tabungan', $productFilter);
                }
            });

        if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
            $transaksiQuery->whereBetween('tanggal_transaksi', [
                $dateRange['start_date'],
                $dateRange['end_date']
            ]);
        }

        $transactionData = $transaksiQuery->orderBy('tanggal_transaksi', 'desc')->get();
        $stats = $service->getSavingsStats();

        $html = $this->generateTransactionReportHtml($stats, $transactionData, $dateRange, $productFilter);

        return PdfHelper::generatePdf($html, $filename);
    }

    public function exportBulkSavingsReport(Collection $records): string
    {
        $filename = 'bulk-savings-report-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        $html = $this->generateBulkSavingsReportHtml($records);

        return PdfHelper::generatePdf($html, $filename);
    }

    private function generateSavingsReportHtml(array $stats, Collection $savingsData, array $dateRange, $productFilter): string
    {
        $startDate = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $endDate = Carbon::parse($dateRange['end_date'])->format('d M Y');
        $productName = $productFilter ? \App\Models\ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Tabungan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .stat-value { font-size: 16px; font-weight: bold; color: #2563eb; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TABUNGAN</h1>
                <p>Periode: ' . $startDate . ' - ' . $endDate . '</p>
                <p>Produk: ' . $productName . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">' . number_format($stats['total_accounts']) . '</div>
                    <div class="stat-label">Total Rekening Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_balance'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Saldo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['avg_balance'], 0, ',', '.') . '</div>
                    <div class="stat-label">Rata-rata Saldo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">' . number_format($stats['transaction_count']) . '</div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Tabungan</th>
                        <th>Nama Nasabah</th>
                        <th>Produk</th>
                        <th>Saldo</th>
                        <th>Tanggal Buka</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($savingsData as $tabungan) {
            $html .= '
                    <tr>
                        <td>' . $tabungan->no_tabungan . '</td>
                        <td>' . ($tabungan->profile?->user?->name ?? 'N/A') . '</td>
                        <td>' . ($tabungan->produkTabungan?->nama_produk ?? 'N/A') . '</td>
                        <td class="text-right">Rp ' . number_format($tabungan->saldo, 0, ',', '.') . '</td>
                        <td>' . Carbon::parse($tabungan->tanggal_buka_rekening)->format('d M Y') . '</td>
                        <td>' . ucfirst($tabungan->status_rekening) . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function generateTransactionReportHtml(array $stats, Collection $transactionData, array $dateRange, $productFilter): string
    {
        $startDate = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $endDate = Carbon::parse($dateRange['end_date'])->format('d M Y');
        $productName = $productFilter ? \App\Models\ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Transaksi Tabungan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .stat-value { font-size: 16px; font-weight: bold; color: #2563eb; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .debit { color: #10b981; }
                .kredit { color: #f59e0b; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TRANSAKSI TABUNGAN</h1>
                <p>Periode: ' . $startDate . ' - ' . $endDate . '</p>
                <p>Produk: ' . $productName . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_deposits'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Setoran (' . number_format($stats['deposit_count']) . ' transaksi)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_withdrawals'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Penarikan (' . number_format($stats['withdrawal_count']) . ' transaksi)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_deposits'] - $stats['total_withdrawals'], 0, ',', '.') . '</div>
                    <div class="stat-label">Net Flow</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Tabungan</th>
                        <th>Nasabah</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Teller</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($transactionData as $transaksi) {
            $jenisClass = $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'debit' : 'kredit';
            $jenisText = $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'Setoran' : 'Penarikan';

            $html .= '
                    <tr>
                        <td>' . Carbon::parse($transaksi->tanggal_transaksi)->format('d M Y H:i') . '</td>
                        <td>' . $transaksi->tabungan->no_tabungan . '</td>
                        <td>' . ($transaksi->tabungan->profile?->user?->name ?? 'N/A') . '</td>
                        <td class="' . $jenisClass . '">' . $jenisText . '</td>
                        <td class="text-right">Rp ' . number_format($transaksi->jumlah, 0, ',', '.') . '</td>
                        <td>' . ($transaksi->keterangan ?? '-') . '</td>
                        <td>' . ($transaksi->admin?->name ?? 'N/A') . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function generateBulkSavingsReportHtml(Collection $records): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Tabungan Terpilih</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TABUNGAN TERPILIH</h1>
                <p>Total Rekening: ' . $records->count() . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Tabungan</th>
                        <th>Nama Nasabah</th>
                        <th>Produk</th>
                        <th>Saldo</th>
                        <th>Tanggal Buka</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($records as $tabungan) {
            $html .= '
                    <tr>
                        <td>' . $tabungan->no_tabungan . '</td>
                        <td>' . ($tabungan->profile?->user?->name ?? 'N/A') . '</td>
                        <td>' . ($tabungan->produkTabungan?->nama_produk ?? 'N/A') . '</td>
                        <td class="text-right">Rp ' . number_format($tabungan->saldo, 0, ',', '.') . '</td>
                        <td>' . Carbon::parse($tabungan->tanggal_buka_rekening)->format('d M Y') . '</td>
                        <td>' . ucfirst($tabungan->status_rekening) . '</td>
                    </tr>';
        }

        $totalSaldo = $records->sum('saldo');
        $html .= '
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="3">TOTAL</td>
                        <td class="text-right">Rp ' . number_format($totalSaldo, 0, ',', '.') . '</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';

        return $html;
    }
    /**
     * Versi chunked untuk generate savings report (hemat memory).
     * Gunakan ketika data sangat besar. Progress bisa diterima via callback.
     *
     * Contoh penggunaan di controller:
     * $service = (new SavingsReportExportService())
     *              ->setProgressCallback(function($done,$total,$percent){ Cache::put('savings_report_progress', compact('done','total','percent')); });
     * $filepath = $service->generateSavingsReportFileChunked($productId, $dateRange, $filename, 500);
     */
    public function generateSavingsReportFileChunked($productFilter, array $dateRange, string $filename, int $chunkSize = 100, int $memoryLimitMB = 512): string
    {
        $this->setMemoryLimit($memoryLimitMB);
        $this->logMemoryUsage('savings_chunk_start');

        try {
            $service = new SavingsReportService($productFilter, $dateRange);
            $baseQuery = $service->getActiveSavingsQuery()->orderBy('id_tabungan');
            $total = (clone $baseQuery)->count();
            $processed = 0;
            $savingsLean = [];

            $baseQuery->chunkById($chunkSize, function ($chunk) use (&$processed, $total, &$savingsLean) {
                foreach ($chunk as $tabungan) {
                    $savingsLean[] = $this->leanSanitizeModel($tabungan);
                }
                $processed += $chunk->count();
                $this->reportProgress($processed, $total);
                // Free chunk models
                unset($chunk);
                if ($processed % (1000) === 0) {
                    gc_collect_cycles();
                    $this->logMemoryUsage('savings_chunk_' . $processed);
                }
            });

            $stats = $this->cleanArrayData($service->getSavingsStats());

            $html = $this->generateSavingsReportHtmlFromLean($stats, $savingsLean, $dateRange, $productFilter);

            $this->reportProgress($total, $total);
            $this->logMemoryUsage('savings_chunk_end');

            return PdfHelper::generatePdf($html, $filename);
        } catch (\Exception $e) {
            Log::error('Chunked savings report generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
            ]);
            throw $e;
        }
    }

    /**
     * Versi chunked untuk generate transaction report dengan lean array.
     */
    public function generateTransactionReportFileChunked($productFilter, array $dateRange, string $filename, int $chunkSize = 500, int $memoryLimitMB = 512): string
    {
        $this->setMemoryLimit($memoryLimitMB);
        $this->logMemoryUsage('trx_savings_chunk_start');

        try {
            $service = new SavingsReportService($productFilter, $dateRange);

            $query = TransaksiTabungan::query()
                ->with(['tabungan.profile.user', 'tabungan.produkTabungan', 'admin'])
                ->whereHas('tabungan', function ($q) use ($productFilter) {
                    $q->where('status_rekening', 'aktif');
                    if ($productFilter) {
                        $q->where('produk_tabungan', $productFilter);
                    }
                });

            if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
                $query->whereBetween('tanggal_transaksi', [
                    $dateRange['start_date'],
                    $dateRange['end_date']
                ]);
            }

            $query->orderBy('id');

            $total = (clone $query)->count();
            $processed = 0;
            $transactionsLean = [];

            $query->chunkById($chunkSize, function ($chunk) use (&$processed, $total, &$transactionsLean) {
                foreach ($chunk as $transaksi) {
                    $transactionsLean[] = $this->leanSanitizeModel($transaksi);
                }
                $processed += $chunk->count();
                $this->reportProgress($processed, $total);
                unset($chunk);
                if ($processed % (1000) === 0) {
                    gc_collect_cycles();
                    $this->logMemoryUsage('trx_savings_chunk_' . $processed);
                }
            });

            $stats = $this->cleanArrayData($service->getSavingsStats());

            $html = $this->generateTransactionReportHtmlFromLean($stats, $transactionsLean, $dateRange, $productFilter);

            $this->reportProgress($total, $total);
            $this->logMemoryUsage('trx_savings_chunk_end');

            return PdfHelper::generatePdf($html, $filename);
        } catch (\Exception $e) {
            Log::error('Chunked transaction report generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
            ]);
            throw $e;
        }
    }

    /**
     * Set / adjust memory limit & execution time for heavy export.
     */
    private function setMemoryLimit(int $memoryLimitMB = 512): void
    {
        try {
            $current = ini_get('memory_limit');
            $desired = $memoryLimitMB . 'M';
            if ($current !== '-1') {
                @ini_set('memory_limit', $desired);
            }
            @set_time_limit(0);
        } catch (\Throwable $e) {
            Log::warning('Cannot adjust memory/time limit: ' . $e->getMessage());
        }
    }

    /**
     * Simple memory logger (debug only).
     */
    private function logMemoryUsage(string $label): void
    {
        Log::debug('MemoryUsage', [
            'label' => $label,
            'usage_mb' => round(memory_get_usage(true) / 1048576, 2),
            'peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2)
        ]);
    }

    /**
     * Convert Model (and minimal relations) to lean sanitized array to reduce memory.
     */
    private function leanSanitizeModel(\Illuminate\Database\Eloquent\Model $model): array
    {
        $data = [];
        foreach ($model->getAttributes() as $k => $v) {
            if (is_string($v)) {
                $data[$k] = $this->sanitizeString($v);
            } else {
                if (is_scalar($v) || is_null($v)) {
                    $data[$k] = $v;
                } else {
                    $data[$k] = (string) $v; // fallback cast
                }
            }
        }

        // Relation attribute whitelist for savings domain
        $relationExtraKeys = ['no_tabungan', 'nama_produk', 'saldo', 'status_rekening', 'jenis_transaksi', 'jumlah'];

        foreach ($model->getRelations() as $relName => $relVal) {
            if ($relVal instanceof \Illuminate\Database\Eloquent\Model) {
                $relArr = ['id' => $relVal->getAttribute('id')];
                // capture generic name fields
                foreach (['name', 'nama', 'nama_lengkap', 'full_name', 'no_tabungan', 'nama_produk'] as $cand) {
                    if (!empty($relVal->getAttribute($cand))) {
                        $relArr[$cand] = $this->sanitizeString((string)$relVal->getAttribute($cand));
                    }
                }
                // capture whitelisted extras
                foreach ($relationExtraKeys as $ek) {
                    if (!array_key_exists($ek, $relArr) && !empty($relVal->getAttribute($ek))) {
                        $relArr[$ek] = $relVal->getAttribute($ek);
                    }
                }
                $data[$relName] = $relArr;

                // Flatten frequently used nested chains (profile->user, tabungan->profile->user, tabungan->produkTabungan)
                if (method_exists($relVal, 'getRelations')) {
                    // Eager loaded nested user
                    if ($relVal->relationLoaded('user') && ($userRel = $relVal->getRelation('user')) instanceof \Illuminate\Database\Eloquent\Model) {
                        $userName = $userRel->getAttribute('name');
                        if ($userName) {
                            $data['user_name'] = $this->sanitizeString((string)$userName);
                        }
                    }
                    // If this relation is 'tabungan', also try to expose nested relations for easy access
                    if ($relName === 'tabungan') {
                        if ($relVal->relationLoaded('profile') && ($prof = $relVal->getRelation('profile')) instanceof \Illuminate\Database\Eloquent\Model) {
                            if ($prof->relationLoaded('user') && ($userRel = $prof->getRelation('user')) instanceof \Illuminate\Database\Eloquent\Model) {
                                $userName = $userRel->getAttribute('name');
                                if ($userName) {
                                    $data['tabungan_user_name'] = $this->sanitizeString((string)$userName);
                                }
                            }
                        }
                        if ($relVal->relationLoaded('produkTabungan') && ($prod = $relVal->getRelation('produkTabungan')) instanceof \Illuminate\Database\Eloquent\Model) {
                            $prodName = $prod->getAttribute('nama_produk');
                            if ($prodName) {
                                $data['tabungan_produk_nama'] = $this->sanitizeString((string)$prodName);
                            }
                        }
                    }
                }
            } elseif ($relVal instanceof \Illuminate\Support\Collection) {
                $data[$relName] = $relVal->pluck('id')->filter()->values()->all();
            }
        }
        return $data;
    }

    /**
     * Clean array data recursively
     */
    private function cleanArrayData(array $array): array
    {
        $cleaned = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = $this->cleanArrayData($value);
            } elseif (is_string($value)) {
                $cleaned[$key] = $this->sanitizeString($value);
            } elseif (is_object($value)) {
                $cleaned[$key] = $value; // Keep objects as-is for stats
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    /**
     * Sanitize string for PDF generation with robust UTF-8 handling
     */
    private function sanitizeString(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        // Convert to string if not already
        $string = (string) $string;

        // First, try to detect and fix encoding issues
        $encoding = mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);

        if ($encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding ?: 'UTF-8');
        }

        // Remove any malformed UTF-8 sequences by converting to UTF-8 again
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // Replace problematic smart quotes and dashes with regular characters
        $string = str_replace([
            chr(226) . chr(128) . chr(156), // Left double quote
            chr(226) . chr(128) . chr(157), // Right double quote
            chr(226) . chr(128) . chr(152), // Left single quote
            chr(226) . chr(128) . chr(153), // Right single quote
            chr(226) . chr(128) . chr(147), // Em dash
            chr(226) . chr(128) . chr(148), // En dash
        ], ['"', '"', "'", "'", '-', '-'], $string);

        // Remove control characters except newlines, carriage returns, and tabs
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);

        // Ensure the string is valid UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            // If still not valid, remove all non-ASCII characters as fallback
            $string = preg_replace('/[^\x20-\x7E\r\n\t]/', '', $string);
        }

        return trim($string) ?: '';
    }

    /**
     * Generate savings report HTML from lean array data
     */
    private function generateSavingsReportHtmlFromLean(array $stats, array $savingsLean, array $dateRange, $productFilter): string
    {
        $startDate = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $endDate = Carbon::parse($dateRange['end_date'])->format('d M Y');
        $productName = $productFilter ? ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Tabungan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .stat-value { font-size: 16px; font-weight: bold; color: #2563eb; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TABUNGAN</h1>
                <p>Periode: ' . $startDate . ' - ' . $endDate . '</p>
                <p>Produk: ' . $productName . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">' . number_format($stats['total_accounts']) . '</div>
                    <div class="stat-label">Total Rekening Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_balance'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Saldo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['avg_balance'], 0, ',', '.') . '</div>
                    <div class="stat-label">Rata-rata Saldo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">' . number_format($stats['transaction_count']) . '</div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Tabungan</th>
                        <th>Nama Nasabah</th>
                        <th>Produk</th>
                        <th>Saldo</th>
                        <th>Tanggal Buka</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($savingsLean as $tabungan) {
            $userName = $tabungan['user_name'] ?? ($tabungan['profile']['nama'] ?? 'N/A');
            $produkNama = $tabungan['produkTabungan']['nama_produk'] ?? 'N/A';

            $html .= '
                    <tr>
                        <td>' . ($tabungan['no_tabungan'] ?? 'N/A') . '</td>
                        <td>' . $userName . '</td>
                        <td>' . $produkNama . '</td>
                        <td class="text-right">Rp ' . number_format($tabungan['saldo'] ?? 0, 0, ',', '.') . '</td>
                        <td>' . Carbon::parse($tabungan['tanggal_buka_rekening'])->format('d M Y') . '</td>
                        <td>' . ucfirst($tabungan['status_rekening'] ?? 'N/A') . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Generate transaction report HTML from lean array data
     */
    private function generateTransactionReportHtmlFromLean(array $stats, array $transactionsLean, array $dateRange, $productFilter): string
    {
        $startDate = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $endDate = Carbon::parse($dateRange['end_date'])->format('d M Y');
        $productName = $productFilter ? ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Transaksi Tabungan</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; color: #666; }
                .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                .stat-value { font-size: 16px; font-weight: bold; color: #2563eb; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .debit { color: #10b981; }
                .kredit { color: #f59e0b; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LAPORAN TRANSAKSI TABUNGAN</h1>
                <p>Periode: ' . $startDate . ' - ' . $endDate . '</p>
                <p>Produk: ' . $productName . '</p>
                <p>Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s') . '</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_deposits'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Setoran (' . number_format($stats['deposit_count']) . ' transaksi)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_withdrawals'], 0, ',', '.') . '</div>
                    <div class="stat-label">Total Penarikan (' . number_format($stats['withdrawal_count']) . ' transaksi)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Rp ' . number_format($stats['total_deposits'] - $stats['total_withdrawals'], 0, ',', '.') . '</div>
                    <div class="stat-label">Net Flow</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Tabungan</th>
                        <th>Nasabah</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Teller</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($transactionsLean as $transaksi) {
            $jenisClass = $transaksi['jenis_transaksi'] === TransaksiTabungan::JENIS_SETORAN ? 'debit' : 'kredit';
            $jenisText = $transaksi['jenis_transaksi'] === TransaksiTabungan::JENIS_SETORAN ? 'Setoran' : 'Penarikan';
            $userName = $transaksi['tabungan_user_name'] ?? ($transaksi['tabungan']['profile']['nama'] ?? 'N/A');
            $adminName = $transaksi['admin']['name'] ?? 'N/A';

            $html .= '
                    <tr>
                        <td>' . Carbon::parse($transaksi['tanggal_transaksi'])->format('d M Y H:i') . '</td>
                        <td>' . ($transaksi['tabungan']['no_tabungan'] ?? 'N/A') . '</td>
                        <td>' . $userName . '</td>
                        <td class="' . $jenisClass . '">' . $jenisText . '</td>
                        <td class="text-right">Rp ' . number_format($transaksi['jumlah'] ?? 0, 0, ',', '.') . '</td>
                        <td>' . ($transaksi['keterangan'] ?? '-') . '</td>
                        <td>' . $adminName . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                <p>Laporan ini digenerate secara otomatis oleh sistem</p>
            </div>
        </body>';
        return $html;
    }
}
