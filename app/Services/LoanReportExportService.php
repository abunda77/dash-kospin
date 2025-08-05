<?php

namespace App\Services;

use App\Models\Pinjaman;
use App\Models\TransaksiPinjaman;
use App\Models\ProdukPinjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoanReportExportService
{    public function exportLoanReport(?string $productFilter = null, array $dateRange = []): \Barryvdh\DomPDF\PDF
    {
        try {
            $service = new LoanReportService($productFilter, $dateRange);
            
            $loans = $service->getApprovedLoansQuery()
                ->orderBy('tanggal_pinjaman', 'desc')
                ->get();

            $stats = $service->getLoanStats();
            
            // Pre-clean all data to ensure UTF-8 compatibility
            $cleanedLoans = $this->cleanDataForPdf($loans);
            $cleanedStats = $this->cleanArrayData($stats);
            
            $data = [
                'pinjamans' => $cleanedLoans,
                'stats' => $cleanedStats,
                'dateRange' => $dateRange,
                'productName' => $this->sanitizeString($productFilter ? ProdukPinjaman::find($productFilter)?->nama_produk : 'Semua Produk'),
                'generatedAt' => Carbon::now()->format('d M Y H:i'),
                'periode' => $this->formatDateRangeDisplay($dateRange),
            ];

            $pdf = Pdf::loadView('reports.laporan-pinjaman', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'portrait',
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
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'portrait',
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
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'chroot' => public_path(),
                    'defaultMediaType' => 'screen',
                    'isFontSubsettingEnabled' => true,
                    'defaultPaperOrientation' => 'portrait',
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
     * Safe string for display (already sanitized)
     */
    private function safeString($value): string
    {
        return $this->sanitizeString($value);
    }
}