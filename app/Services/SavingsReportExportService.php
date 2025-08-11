<?php

namespace App\Services;

use App\Models\TransaksiTabungan;
use App\Models\Tabungan;
use App\Models\ProdukTabungan;
use App\Helpers\PdfHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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
        try {
            $service = new SavingsReportService($productFilter, $dateRange);
            $stats = $service->getSavingsStats();
            $savingsData = $service->getActiveSavingsQuery()->get();

            // Pre-clean all data to ensure UTF-8 compatibility
            $cleanedSavingsData = $this->cleanDataForPdf($savingsData);
            $cleanedStats = $this->cleanArrayData($stats);

            $data = [
                'savingsData' => $cleanedSavingsData,
                'stats' => $cleanedStats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-tabungan', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96,
                ]);

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filepath = $tempDir . '/' . $filename;
            file_put_contents($filepath, $pdf->output());

            // Clean up old files (older than 1 hour)
            $this->cleanupOldTempFiles($tempDir);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Savings report generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    public function generateTransactionReportFile($productFilter, array $dateRange, string $filename): string
    {
        try {
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

            // Pre-clean all data to ensure UTF-8 compatibility
            $cleanedTransactionData = $this->cleanDataForPdf($transactionData);
            $cleanedStats = $this->cleanArrayData($stats);

            $data = [
                'transactionData' => $cleanedTransactionData,
                'stats' => $cleanedStats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-transaksi-tabungan', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96,
                ]);

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filepath = $tempDir . '/' . $filename;
            file_put_contents($filepath, $pdf->output());

            // Clean up old files (older than 1 hour)
            $this->cleanupOldTempFiles($tempDir);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Transaction report generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    public function exportBulkSavingsReport(Collection $records): string
    {
        try {
            $filename = 'bulk-savings-report-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';

            // Pre-clean all data to ensure UTF-8 compatibility
            $cleanedRecords = $this->cleanDataForPdf($records);

            $data = [
                'records' => $cleanedRecords,
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('reports.laporan-tabungan-bulk', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96,
                ]);

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filepath = $tempDir . '/' . $filename;
            file_put_contents($filepath, $pdf->output());

            // Clean up old files (older than 1 hour)
            $this->cleanupOldTempFiles($tempDir);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Bulk savings report generation failed', [
                'error' => $e->getMessage(),
                'record_count' => $records->count(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
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

            $data = [
                'savingsData' => $savingsLean,
                'stats' => $stats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-tabungan', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96,
                ]);

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filepath = $tempDir . '/' . $filename;
            file_put_contents($filepath, $pdf->output());

            // Clean up old files (older than 1 hour)
            $this->cleanupOldTempFiles($tempDir);

            $this->reportProgress($total, $total);
            $this->logMemoryUsage('savings_chunk_end');

            return $filepath;
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

            $data = [
                'transactionData' => $transactionsLean,
                'stats' => $stats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukTabungan::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-transaksi-tabungan', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96,
                ]);

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filepath = $tempDir . '/' . $filename;
            file_put_contents($filepath, $pdf->output());

            // Clean up old files (older than 1 hour)
            $this->cleanupOldTempFiles($tempDir);

            $this->reportProgress($total, $total);
            $this->logMemoryUsage('trx_savings_chunk_end');

            return $filepath;
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
     * Format date range for display in reports
     */
    private function formatDateRangeDisplay(array $dateRange): string
    {
        if (empty($dateRange) || !isset($dateRange['start_date']) || !isset($dateRange['end_date'])) {
            return 'Semua Periode';
        }

        $start = Carbon::parse($dateRange['start_date'])->format('d M Y');
        $end = Carbon::parse($dateRange['end_date'])->format('d M Y');

        if ($start === $end) {
            return $start;
        }

        return $start . ' - ' . $end;
    }

    /**
     * Clean and sanitize data for PDF generation
     * Preserves object structure while cleaning string properties
     */
    private function cleanDataForPdf($data)
    {
        if ($data instanceof \Illuminate\Database\Eloquent\Collection || $data instanceof \Illuminate\Support\Collection) {
            // For collections, map through and clean each item while preserving collection type
            return $data->map(function ($item) {
                return $this->deepCleanObject($item);
            });
        }

        if (is_object($data)) {
            return $this->deepCleanObject($data);
        }

        if (is_array($data)) {
            return $this->cleanArrayData($data);
        }

        return $data;
    }

    /**
     * Deep clean an object and all its relationships
     */
    private function deepCleanObject($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        if ($object instanceof \Illuminate\Database\Eloquent\Model) {
            // Clean all attributes
            $attributes = $object->getAttributes();
            $cleanedAttributes = [];

            foreach ($attributes as $key => $value) {
                if (is_string($value)) {
                    $cleanedAttributes[$key] = $this->sanitizeString($value);
                } else {
                    $cleanedAttributes[$key] = $value;
                }
            }

            // Create new model instance with cleaned attributes
            $cleanObject = $object->replicate();
            $cleanObject->setRawAttributes($cleanedAttributes);

            // Clean relationships recursively
            foreach ($object->getRelations() as $relationName => $relationValue) {
                if ($relationValue instanceof \Illuminate\Database\Eloquent\Collection) {
                    $cleanedRelation = $relationValue->map(function ($item) {
                        return $this->deepCleanObject($item);
                    });
                } else {
                    $cleanedRelation = $this->deepCleanObject($relationValue);
                }
                $cleanObject->setRelation($relationName, $cleanedRelation);
            }

            return $cleanObject;
        }

        // For regular objects, return as-is to avoid issues
        return $object;
    }

    /**
     * Clean up temporary files older than specified time
     */
    private function cleanupOldTempFiles(string $directory, int $maxAgeHours = 1): void
    {
        try {
            $files = glob($directory . '/*');
            $cutoffTime = time() - ($maxAgeHours * 3600);

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup temp files: ' . $e->getMessage());
        }
    }
}
