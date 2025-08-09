<?php

namespace App\Services;

use App\Models\Pinjaman;
use App\Models\TransaksiPinjaman;
use App\Models\ProdukPinjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoanReportExportService
{    
    public function exportLoanReport(?string $productFilter = null, array $dateRange = []): \Barryvdh\DomPDF\PDF
    {
        try {
            $service = new LoanReportService($productFilter, $dateRange);
            
            $loans = $service->getApprovedLoansQuery()
                ->orderBy('tanggal_pinjaman', 'desc')
                ->get();

            $stats = $service->getLoanStats();
            
            // Get additional widget statistics
            $critical90DaysStats = $this->getCritical90DaysStats();
            $productDistribution = $service->getProductDistribution();
            
            // Pre-clean all data to ensure UTF-8 compatibility
            $cleanedLoans = $this->cleanDataForPdf($loans);
            $cleanedStats = $this->cleanArrayData($stats);
            $cleanedCritical90DaysStats = $this->cleanArrayData($critical90DaysStats);
            $cleanedProductDistribution = $this->cleanArrayData($productDistribution);
            
            $data = [
                'pinjamans' => $cleanedLoans,
                'stats' => $cleanedStats,
                'critical90DaysStats' => $cleanedCritical90DaysStats,
                'productDistribution' => $cleanedProductDistribution,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukPinjaman::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-pinjaman', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'landscape',
                    'dpi' => 96,
                ]);
            
            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            throw $e;
        }
    }    public function exportTransactionReport(?string $productFilter = null, array $dateRange = []): \Barryvdh\DomPDF\PDF
    {
        try {
            $service = new LoanReportService($productFilter, $dateRange);
            
            $transactions = TransaksiPinjaman::query()
                ->with(['pinjaman.profile.user', 'pinjaman.produkPinjaman'])
                ->whereHas('pinjaman', function ($q) use ($productFilter) {
                    $q->where('status_pinjaman', LoanReportService::STATUS_APPROVED)
                      ->when($productFilter, fn($query) => $query->where('produk_pinjaman_id', $productFilter));
                })
                ->when($dateRange, function ($query) use ($dateRange) {
                    $query->whereDate('tanggal_pembayaran', '>=', $dateRange['start'])
                          ->whereDate('tanggal_pembayaran', '<=', $dateRange['end']);
                })
                ->orderBy('tanggal_pembayaran', 'desc')
                ->get();

            $stats = $service->getLoanStats();
            
            // Pre-clean all data to ensure UTF-8 compatibility
            $cleanedTransactions = $this->cleanDataForPdf($transactions);
            $cleanedStats = $this->cleanArrayData($stats);
            
            $data = [
                'transactions' => $cleanedTransactions,
                'stats' => $cleanedStats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukPinjaman::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
                'tanggal_cetak' => Carbon::now()->format('d/m/Y'),
                'totalTransactions' => $transactions->count(),
                'totalAmount' => $transactions->sum('total_pembayaran'),
            ];

            $pdf = Pdf::loadView('reports.laporan-transaksi-pinjaman', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'landscape',
                    'dpi' => 96,
                ]);

            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('Transaction PDF generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            throw $e;
        }
    }public function exportBulkLoanReport($records): \Barryvdh\DomPDF\PDF
    {
        try {
            $loanIds = $records->pluck('id')->toArray();
            
            $loans = Pinjaman::query()
                ->with(['profile.user', 'produkPinjaman'])
                ->whereIn('id', $loanIds)
                ->orderBy('tanggal_pinjaman', 'desc')
                ->get();

            $totalAmount = $loans->sum('jumlah_pinjaman');
            $totalLoans = $loans->count();
            $avgAmount = $totalLoans > 0 ? $totalAmount / $totalLoans : 0;            // Clean and encode data properly
            $data = [
                'loans' => $this->cleanDataForPdf($loans),
                'totalAmount' => $totalAmount,
                'totalLoans' => $totalLoans,
                'avgAmount' => $avgAmount,
                'generatedAt' => Carbon::now()->format('d M Y H:i'),
                'reportTitle' => 'Laporan Pinjaman Terpilih',
            ];

            $pdf = Pdf::loadView('reports.laporan-pinjaman-bulk', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'landscape',
                    'dpi' => 96,
                ]);

            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('Bulk PDF generation failed', [
                'error' => $e->getMessage(),
                'loan_ids' => $records->pluck('id')->toArray(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            throw $e;
        }
    }    /**
     * Format date range for display in reports
     */
    private function formatDateRangeDisplay(array $dateRange): string
    {
        if (empty($dateRange) || !isset($dateRange['start']) || !isset($dateRange['end'])) {
            return 'Semua Periode';
        }

        $start = Carbon::parse($dateRange['start'])->format('d M Y');
        $end = Carbon::parse($dateRange['end'])->format('d M Y');
        
        if ($start === $end) {
            return $start;
        }
        
        return $start . ' - ' . $end;
    }    /**
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
                $cleaned[$key] = $this->deepCleanObject($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }    /**
     * Recursively clean array data
     */
    private function recursiveCleanArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->recursiveCleanArray($value);
            } elseif (is_string($value)) {
                $array[$key] = $this->sanitizeString($value);
            } elseif (is_object($value)) {
                $array[$key] = $this->deepCleanObject($value);
            }
        }
        return $array;
    }/**
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
            chr(226).chr(128).chr(156), // Left double quote
            chr(226).chr(128).chr(157), // Right double quote
            chr(226).chr(128).chr(152), // Left single quote
            chr(226).chr(128).chr(153), // Right single quote
            chr(226).chr(128).chr(147), // Em dash
            chr(226).chr(128).chr(148), // En dash
        ], ['"', '"', "'", "'", '-', '-'], $string);
        
        // Remove control characters except newlines, carriage returns, and tabs
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
        
        // Ensure the string is valid UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            // If still not valid, remove all non-ASCII characters as fallback
            $string = preg_replace('/[^\x20-\x7E\r\n\t]/', '', $string);
        }
        
        return trim($string) ?: '';
    }    /**
     * Download PDF directly to browser
     */
    public function downloadLoanReport(?string $productFilter = null, array $dateRange = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $pdf = $this->exportLoanReport($productFilter, $dateRange);
            $filename = 'laporan-pinjaman-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
            
            // Get PDF content as binary string
            $pdfContent = $pdf->output();
            
            // Create a temporary file to store the PDF
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tempFile, $pdfContent);
            
            // Return binary file response
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('PDF download failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            throw $e;
        }
    }

    /**
     * Download transaction report directly to browser
     */
    public function downloadTransactionReport(?string $productFilter = null, array $dateRange = []): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $pdf = $this->exportTransactionReport($productFilter, $dateRange);
            $filename = 'laporan-transaksi-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
            
            // Get PDF content as binary string
            $pdfContent = $pdf->output();
            
            // Create a temporary file to store the PDF
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tempFile, $pdfContent);
            
            // Return binary file response
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Transaction PDF download failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Generate loan report PDF file and save to temporary storage
     */
    public function generateLoanReportFile(?string $productFilter = null, array $dateRange = [], ?string $filename = null): string
    {
        $pdf = $this->exportLoanReport($productFilter, $dateRange);
        
        if (!$filename) {
            $filename = 'laporan-pinjaman-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        }
        
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $filepath = $tempDir . '/' . $filename;
        file_put_contents($filepath, $pdf->output());
        
        // Clean up old files (older than 1 hour)
        $this->cleanupOldTempFiles($tempDir);
        
        return $filepath;
    }
    
    /**
     * Generate transaction report PDF file and save to temporary storage
     */
    public function generateTransactionReportFile(?string $productFilter = null, array $dateRange = [], ?string $filename = null): string
    {
        $pdf = $this->exportTransactionReport($productFilter, $dateRange);
        
        if (!$filename) {
            $filename = 'laporan-transaksi-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
        }
        
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $filepath = $tempDir . '/' . $filename;
        file_put_contents($filepath, $pdf->output());
        
        // Clean up old files (older than 1 hour)
        $this->cleanupOldTempFiles($tempDir);
        
        return $filepath;
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

    /**
     * Format currency for display
     */
    private function formatCurrency($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format date for display
     */
    private function formatDate($date): string
    {
        try {
            return Carbon::parse($date)->format('d M Y');
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Get critical 90 days statistics for widget data
     */
    private function getCritical90DaysStats(): array
    {
        $today = Carbon::today();
        
        $critical90DaysQuery = Pinjaman::query()
            ->with(['profile', 'biayaBungaPinjaman', 'denda', 'transaksiPinjaman'])
            ->where('status_pinjaman', 'approved')
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->where(function ($query) use ($today) {
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 90', [$today]);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereDoesntHave('transaksiPinjaman');
                });
            });
        
        $critical90DaysData = $critical90DaysQuery->get();
        
        $totalAccounts = $critical90DaysData->count();
        $totalOverdue = $critical90DaysData->sum(function ($record) use ($today) {
            $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
            $jumlahBulanTerlambat = ceil($hariTerlambat / 30);
            
            $angsuranPokok = abs($this->calculateAngsuranPokok($record));
            $bungaPerBulan = abs($this->calculateBungaPerBulan($record));
            
            $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;
            $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;
            
            $angsuranTotal = $angsuranPokok + $bungaPerBulan;
            $dendaPerHari = (0.05 * $angsuranTotal) / 30;
            $totalDenda = $dendaPerHari * $hariTerlambat;
            
            return $totalPokok + $totalBunga + $totalDenda;
        });
        
        // Get total active loans for risk percentage calculation
        $totalActiveLoans = Pinjaman::where('status_pinjaman', 'approved')->count();
        $riskPercentage = $totalActiveLoans > 0 ? ($totalAccounts / $totalActiveLoans) * 100 : 0;
        
        // Calculate average overdue days
        $avgOverdueDays = $critical90DaysData->avg(function ($record) use ($today) {
            return abs($this->calculateHariTerlambat($record, $today));
        }) ?? 0;
        
        return [
            'total_accounts' => $totalAccounts,
            'total_overdue' => $totalOverdue,
            'risk_percentage' => $riskPercentage,
            'avg_overdue_days' => $avgOverdueDays,
        ];
    }

    /**
     * Helper function to calculate angsuran pokok
     */
    private function calculateAngsuranPokok($record)
    {
        return $record->jumlah_pinjaman / $record->jangka_waktu;
    }

    /**
     * Helper function to calculate bunga per bulan
     */
    private function calculateBungaPerBulan($record)
    {
        $pokok = $record->jumlah_pinjaman;
        $bungaPerTahun = $record->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $record->jangka_waktu;

        // Hitung bunga per bulan (total bunga setahun dibagi jangka waktu)
        return ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;
    }

    /**
     * Helper function to calculate hari terlambat
     */
    private function calculateHariTerlambat($record, $today)
    {
        // Ambil tanggal jatuh tempo dari transaksi terakhir atau tanggal pinjaman
        $lastTransaction = $record->transaksiPinjaman()
            ->orderBy('angsuran_ke', 'desc')
            ->first();

        if ($lastTransaction) {
            // Jika ada transaksi sebelumnya, gunakan tanggal jatuh tempo berikutnya
            $tanggalJatuhTempo = Carbon::parse($lastTransaction->tanggal_pembayaran)
                ->addMonth()
                ->startOfDay();
        } else {
            // Jika belum ada transaksi, gunakan tanggal pinjaman + 1 bulan
            $tanggalJatuhTempo = Carbon::parse($record->tanggal_pinjaman)
                ->addMonth()
                ->startOfDay();
        }

        // Jika masih dalam bulan yang sama dengan tanggal pinjaman, return 0
        if ($today->format('Y-m') === Carbon::parse($record->tanggal_pinjaman)->format('Y-m')) {
            return 0;
        }

        // Hitung keterlambatan hanya jika sudah melewati tanggal jatuh tempo
        // dan berada di bulan yang berbeda
        if ($today->gt($tanggalJatuhTempo) &&
            $today->format('Y-m') !== $tanggalJatuhTempo->format('Y-m')) {
            return $today->diffInDays($tanggalJatuhTempo);
        }

        return 0;
    }

    /**
     * Optional progress callback. Signature: function(int $processed, int $total, float $percent)
     * Dapat di-set dari controller / job untuk update progress bar (misal simpan ke cache / broadcast event).
     */
    protected $progressCallback = null;

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

    /**
     * Versi chunked untuk export loan report (hemat memory).
     * Gunakan ketika data sangat besar. Progress bisa diterima via callback.
     *
     * Contoh penggunaan di controller:
     * $service = (new LoanReportExportService())
     *              ->setProgressCallback(function($done,$total,$percent){ Cache::put('loan_report_progress', compact('done','total','percent')); });
     * $pdf = $service->exportLoanReportChunked($productId, $dateRange, 500);
     */
    public function exportLoanReportChunked(?string $productFilter = null, array $dateRange = [], int $chunkSize = 100, int $memoryLimitMB = 1024): \Barryvdh\DomPDF\PDF
    {
        $this->setMemoryLimit($memoryLimitMB);
        $this->logMemoryUsage('loan_chunk_start');
        try {
            $service = new LoanReportService($productFilter, $dateRange);
            $baseQuery = $service->getApprovedLoansQuery()->orderBy('id_pinjaman');
            $total = (clone $baseQuery)->count();
            $processed = 0;
            $loansLean = [];

            $baseQuery->chunkById($chunkSize, function ($chunk) use (&$processed, $total, &$loansLean) {
                foreach ($chunk as $loan) {
                    $loansLean[] = $this->leanSanitizeModel($loan);
                }
                $processed += $chunk->count();
                $this->reportProgress($processed, $total);
                // Free chunk models
                unset($chunk);
                if ($processed % (1000) === 0) { gc_collect_cycles(); $this->logMemoryUsage('loan_chunk_'.$processed); }
            });

            $stats = $this->cleanArrayData($service->getLoanStats());
            $critical90DaysStats = $this->cleanArrayData($this->getCritical90DaysStats());
            $productDistribution = $this->cleanArrayData($service->getProductDistribution());

            $data = [
                'pinjamans' => $loansLean,
                'stats' => $stats,
                'critical90DaysStats' => $critical90DaysStats,
                'productDistribution' => $productDistribution,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukPinjaman::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-pinjaman', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'landscape',
                    'dpi' => 96,
                ]);

            $this->reportProgress($total, $total);
            $this->logMemoryUsage('loan_chunk_end');
            return $pdf;
        } catch (\Exception $e) {
            Log::error('Chunked loan PDF generation failed', [
                'error' => $e->getMessage(),
                'productFilter' => $productFilter,
                'dateRange' => $dateRange,
            ]);
            throw $e;
        }
    }

    /**
     * Versi chunked untuk export transaction report dengan lean array.
     */
    public function exportTransactionReportChunked(?string $productFilter = null, array $dateRange = [], int $chunkSize = 500, int $memoryLimitMB = 512): \Barryvdh\DomPDF\PDF
    {
        $this->setMemoryLimit($memoryLimitMB);
        $this->logMemoryUsage('trx_chunk_start');
        try {
            $service = new LoanReportService($productFilter, $dateRange);
            $query = TransaksiPinjaman::query()
                ->with(['pinjaman.profile.user', 'pinjaman.produkPinjaman'])
                ->whereHas('pinjaman', function ($q) use ($productFilter) {
                    $q->where('status_pinjaman', LoanReportService::STATUS_APPROVED)
                      ->when($productFilter, fn($query) => $query->where('produk_pinjaman_id', $productFilter));
                })
                ->when($dateRange, function ($query) use ($dateRange) {
                    $query->whereDate('tanggal_pembayaran', '>=', $dateRange['start'])
                          ->whereDate('tanggal_pembayaran', '<=', $dateRange['end']);
                })
                ->orderBy('id');

            $total = (clone $query)->count();
            $processed = 0;
            $trxLean = [];

            $query->chunkById($chunkSize, function ($chunk) use (&$processed, $total, &$trxLean) {
                foreach ($chunk as $trx) {
                    $trxLean[] = $this->leanSanitizeModel($trx);
                }
                $processed += $chunk->count();
                $this->reportProgress($processed, $total);
                unset($chunk);
                if ($processed % (1000) === 0) { gc_collect_cycles(); $this->logMemoryUsage('trx_chunk_'.$processed); }
            });

            $stats = $this->cleanArrayData($service->getLoanStats());

            $data = [
                'transactions' => $trxLean,
                'stats' => $stats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukPinjaman::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
                'tanggal_cetak' => Carbon::now()->format('d/m/Y'),
                'totalTransactions' => count($trxLean),
                'totalAmount' => array_sum(array_column($trxLean, 'total_pembayaran')),
            ];

            $pdf = Pdf::loadView('reports.laporan-transaksi-pinjaman', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'landscape',
                    'dpi' => 96,
                ]);

            $this->reportProgress($total, $total);
            $this->logMemoryUsage('trx_chunk_end');
            return $pdf;
        } catch (\Exception $e) {
            Log::error('Chunked transaction PDF generation failed', [
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
            Log::warning('Cannot adjust memory/time limit: '.$e->getMessage());
        }
    }

    /**
     * Simple memory logger (debug only).
     */
    private function logMemoryUsage(string $label): void
    {
        Log::debug('MemoryUsage', [
            'label' => $label,
            'usage_mb' => round(memory_get_usage(true)/1048576,2),
            'peak_mb' => round(memory_get_peak_usage(true)/1048576,2)
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

        // Relation attribute whitelist / pattern for domain specific needs
        $relationExtraKeys = ['no_pinjaman','nama_produk','jumlah_pinjaman','status_pinjaman'];

        foreach ($model->getRelations() as $relName => $relVal) {
            if ($relVal instanceof \Illuminate\Database\Eloquent\Model) {
                $relArr = ['id' => $relVal->getAttribute('id')];
                // capture generic name fields
                foreach (['name','nama','nama_lengkap','full_name','no_pinjaman','nama_produk'] as $cand) {
                    if (!empty($relVal->getAttribute($cand))) {
                        $relArr[$cand] = $this->sanitizeString((string)$relVal->getAttribute($cand));
                    }
                }
                // capture whitelisted extras
                foreach ($relationExtraKeys as $ek) {
                    if (!array_key_exists($ek,$relArr) && !empty($relVal->getAttribute($ek))) {
                        $relArr[$ek] = $relVal->getAttribute($ek);
                    }
                }
                $data[$relName] = $relArr;

                // Flatten frequently used nested chains (profile->user, pinjaman->profile->user, pinjaman->produkPinjaman)
                if (method_exists($relVal,'getRelations')) {
                    // Eager loaded nested user
                    if ($relVal->relationLoaded('user') && ($userRel = $relVal->getRelation('user')) instanceof \Illuminate\Database\Eloquent\Model) {
                        $userName = $userRel->getAttribute('name');
                        if ($userName) {
                            $data['user_name'] = $this->sanitizeString((string)$userName);
                        }
                    }
                    // If this relation is 'pinjaman', also try to expose nested relations for easy access
                    if ($relName === 'pinjaman') {
                        if ($relVal->relationLoaded('profile') && ($prof = $relVal->getRelation('profile')) instanceof \Illuminate\Database\Eloquent\Model) {
                            if ($prof->relationLoaded('user') && ($userRel = $prof->getRelation('user')) instanceof \Illuminate\Database\Eloquent\Model) {
                                $userName = $userRel->getAttribute('name');
                                if ($userName) {
                                    $data['pinjaman_user_name'] = $this->sanitizeString((string)$userName);
                                }
                            }
                        }
                        if ($relVal->relationLoaded('produkPinjaman') && ($prod = $relVal->getRelation('produkPinjaman')) instanceof \Illuminate\Database\Eloquent\Model) {
                            $prodName = $prod->getAttribute('nama_produk');
                            if ($prodName) {
                                $data['pinjaman_produk_nama'] = $this->sanitizeString((string)$prodName);
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
}