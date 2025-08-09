<?php

namespace App\Console\Commands;

use App\Models\ProdukPinjaman;
use App\Services\LoanReportExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportLoanReportCommand extends Command
{
    protected $signature = 'report:export-loan 
                            {--type=loan : Report type (loan, transaction, bulk)}
                            {--product= : Product ID filter}
                            {--start-date= : Start date (Y-m-d format)}
                            {--end-date= : End date (Y-m-d format)}
                            {--chunk-size=100 : Chunk size for processing}
                            {--memory-limit=1024 : Memory limit in MB}
                            {--public : Save to public storage for download link}';

    protected $description = 'Export loan reports to PDF with progress tracking';

    private LoanReportExportService $exportService;
    private string $progressKey;
    private $progressBar;

    public function __construct()
    {
        parent::__construct();
        $this->exportService = new LoanReportExportService();
    }

    public function handle(): int
    {
        $this->info('🚀 Starting PDF export process...');

        // Initialize progress tracking
        $this->progressKey = 'pdf_export_progress_' . uniqid();
        $this->initializeProgress();
        
        // Display progress key for monitoring
        $this->newLine();
        $this->line("🔍 <info>Progress Key:</info> <comment>{$this->progressKey}</comment>");
        $this->line("🌐 <info>Monitor at:</info> <comment>" . url('/export-monitor?key=' . $this->progressKey) . "</comment>");
        $this->newLine();

        try {
            // Parse and validate options
            $options = $this->parseOptions();

            // Set up progress callback
            $this->exportService->setProgressCallback([$this, 'updateProgress']);

            // Generate PDF based on type
            $pdfPath = $this->generatePdf($options);

            // Handle public storage if requested
            $downloadUrl = $this->handlePublicStorage($pdfPath, $options);

            $this->displayResults($pdfPath, $downloadUrl, $options);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Export failed: {$e->getMessage()}");
            Log::error('PDF export command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        } finally {
            // Clean up progress cache
            Cache::forget($this->progressKey);
        }
    }

    private function parseOptions(): array
    {
        $type = $this->option('type');
        $productId = $this->option('product');
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');
        $chunkSize = (int) $this->option('chunk-size');
        $memoryLimit = (int) $this->option('memory-limit');
        $isPublic = $this->option('public');

        // Validate type
        if (!in_array($type, ['loan', 'transaction', 'bulk'])) {
            throw new \InvalidArgumentException('Invalid report type. Use: loan, transaction, or bulk');
        }

        // Validate product if provided
        if ($productId && !ProdukPinjaman::find($productId)) {
            throw new \InvalidArgumentException("Product with ID {$productId} not found");
        }

        // Parse date range
        $dateRange = [];
        if ($startDate && $endDate) {
            try {
                $dateRange = [
                    'start' => Carbon::parse($startDate)->format('Y-m-d'),
                    'end' => Carbon::parse($endDate)->format('Y-m-d')
                ];
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format. Use Y-m-d format (e.g., 2024-01-01)');
            }
        }

        return [
            'type' => $type,
            'productId' => $productId,
            'dateRange' => $dateRange,
            'chunkSize' => max(10, $chunkSize),
            'memoryLimit' => max(256, $memoryLimit),
            'isPublic' => $isPublic
        ];
    }

    private function generatePdf(array $options): string
    {
        $this->info("📊 Generating {$options['type']} report PDF...");

        switch ($options['type']) {
            case 'loan':
                return $this->generateLoanReport($options);
            case 'transaction':
                return $this->generateTransactionReport($options);
            case 'bulk':
                throw new \InvalidArgumentException('Bulk export requires specific loan IDs - not supported in CLI');
            default:
                throw new \InvalidArgumentException('Unsupported report type');
        }
    }

    private function generateLoanReport(array $options): string
    {
        // Use chunked version for better memory management
        $pdf = $this->exportService->exportLoanReportChunked(
            $options['productId'],
            $options['dateRange'],
            $options['chunkSize'],
            $options['memoryLimit']
        );

        return $this->savePdf($pdf, 'laporan-pinjaman', $options);
    }

    private function generateTransactionReport(array $options): string
    {
        // Use chunked version for better memory management
        $pdf = $this->exportService->exportTransactionReportChunked(
            $options['productId'],
            $options['dateRange'],
            $options['chunkSize'],
            $options['memoryLimit']
        );

        return $this->savePdf($pdf, 'laporan-transaksi', $options);
    }

    private function savePdf($pdf, string $prefix, array $options): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $productName = $options['productId']
            ? ProdukPinjaman::find($options['productId'])?->nama_produk ?? 'unknown'
            : 'semua-produk';

        $filename = "{$prefix}-{$productName}-{$timestamp}.pdf";
        $filename = $this->sanitizeFilename($filename);

        if ($options['isPublic']) {
            // Save to public storage
            $path = "reports/{$filename}";
            Storage::disk('public')->put($path, $pdf->output());
            return storage_path("app/public/{$path}");
        } else {
            // Save to temp directory
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filepath = "{$tempDir}/{$filename}";
            file_put_contents($filepath, $pdf->output());
            return $filepath;
        }
    }

    private function handlePublicStorage(string $pdfPath, array $options): ?string
    {
        if (!$options['isPublic']) {
            return null;
        }

        // Generate public URL
        $relativePath = str_replace(storage_path('app/public/'), '', $pdfPath);
        $downloadUrl = asset("storage/{$relativePath}");

        $this->info("🌐 PDF saved to public storage");
        $this->line("📁 File: " . basename($pdfPath));

        return $downloadUrl;
    }

    private function displayResults(string $pdfPath, ?string $downloadUrl, array $options): void
    {
        $this->newLine();
        $this->info('✅ PDF export completed successfully!');
        $this->newLine();

        // Display file info
        $fileSize = $this->formatFileSize(filesize($pdfPath));
        $this->line("📄 <info>File:</info> " . basename($pdfPath));
        $this->line("📏 <info>Size:</info> {$fileSize}");
        $this->line("📂 <info>Path:</info> {$pdfPath}");

        if ($downloadUrl) {
            $this->newLine();
            $this->line("🔗 <info>Public Download URL:</info>");
            $this->line("   <comment>{$downloadUrl}</comment>");
            $this->newLine();
            $this->line("💡 <info>Note:</info> The file will be accessible via web browser");
        } else {
            $this->newLine();
            $this->line("💡 <info>Tip:</info> Use --public flag to generate a public download link");
        }

        // Display report details
        $this->newLine();
        $this->line("📊 <info>Report Details:</info>");
        $this->line("   Type: " . ucfirst($options['type']) . " Report");

        if ($options['productId']) {
            $productName = ProdukPinjaman::find($options['productId'])?->nama_produk ?? 'Unknown';
            $this->line("   Product: {$productName}");
        } else {
            $this->line("   Product: All Products");
        }

        if (!empty($options['dateRange'])) {
            $this->line("   Period: {$options['dateRange']['start']} to {$options['dateRange']['end']}");
        } else {
            $this->line("   Period: All Time");
        }

        $this->line("   Generated: " . Carbon::now()->format('d M Y H:i:s'));
    }

    public function updateProgress(int $processed, int $total, float $percent): void
    {
        // Update cache for external monitoring
        Cache::put($this->progressKey, [
            'processed' => $processed,
            'total' => $total,
            'percent' => $percent,
            'updated_at' => now()
        ], 300); // 5 minutes TTL

        // Update console progress bar
        if (!isset($this->progressBar)) {
            $this->progressBar = $this->output->createProgressBar($total);
            $this->progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }

        $this->progressBar->setProgress($processed);

        if ($processed >= $total) {
            $this->progressBar->finish();
            $this->newLine();
        }
    }

    private function initializeProgress(): void
    {
        Cache::put($this->progressKey, [
            'processed' => 0,
            'total' => 0,
            'percent' => 0,
            'status' => 'initializing',
            'started_at' => now()
        ], 300);
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        return trim($filename, '-');
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
