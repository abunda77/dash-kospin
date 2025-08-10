<?php

namespace App\Console\Commands;

use App\Models\ProdukTabungan;
use App\Services\SavingsReportExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportSavingsReportCommand extends Command
{
    protected $signature = 'report:export-savings 
                            {--type=savings : Report type (savings, transaction, bulk)}
                            {--product= : Product ID filter}
                            {--start-date= : Start date (Y-m-d format)}
                            {--end-date= : End date (Y-m-d format)}
                            {--chunk-size=100 : Chunk size for processing}
                            {--memory-limit=1024 : Memory limit in MB}
                            {--public : Save to public storage for download link}';

    protected $description = 'Export savings reports to PDF with progress tracking';

    private SavingsReportExportService $exportService;
    private string $progressKey;
    private $progressBar;

    public function __construct()
    {
        parent::__construct();
        $this->exportService = new SavingsReportExportService();
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
        if (!in_array($type, ['savings', 'transaction', 'bulk'])) {
            throw new \InvalidArgumentException('Invalid report type. Use: savings, transaction, or bulk');
        }

        // Validate product if provided
        if ($productId && !ProdukTabungan::find($productId)) {
            throw new \InvalidArgumentException("Product with ID {$productId} not found");
        }

        // Parse date range
        $dateRange = [];
        if ($startDate && $endDate) {
            try {
                $dateRange = [
                    'start_date' => Carbon::parse($startDate)->format('Y-m-d'),
                    'end_date' => Carbon::parse($endDate)->format('Y-m-d')
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
            case 'savings':
                return $this->generateSavingsReport($options);
            case 'transaction':
                return $this->generateTransactionReport($options);
            case 'bulk':
                throw new \InvalidArgumentException('Bulk export requires specific savings IDs - not supported in CLI');
            default:
                throw new \InvalidArgumentException('Unsupported report type');
        }
    }

    private function generateSavingsReport(array $options): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "laporan-tabungan-{$timestamp}.pdf";
        
        $pdfPath = $this->exportService->generateSavingsReportFile(
            $options['productId'],
            $options['dateRange'],
            $filename
        );

        return $pdfPath;
    }

    private function generateTransactionReport(array $options): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "laporan-transaksi-tabungan-{$timestamp}.pdf";
        
        $pdfPath = $this->exportService->generateTransactionReportFile(
            $options['productId'],
            $options['dateRange'],
            $filename
        );

        return $pdfPath;
    }

    private function handlePublicStorage(string $pdfPath, array $options): ?string
    {
        if (!$options['isPublic']) {
            return null;
        }

        // Copy to public storage
        $filename = basename($pdfPath);
        $publicPath = "reports/{$filename}";
        
        Storage::disk('public')->put($publicPath, file_get_contents($pdfPath));
        
        // Generate public URL
        $downloadUrl = asset("storage/{$publicPath}");

        $this->info("🌐 PDF saved to public storage");
        $this->line("📁 File: " . $filename);

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
            $productName = ProdukTabungan::find($options['productId'])?->nama_produk ?? 'Unknown';
            $this->line("   Product: {$productName}");
        } else {
            $this->line("   Product: All Products");
        }

        if (!empty($options['dateRange'])) {
            $this->line("   Period: {$options['dateRange']['start_date']} to {$options['dateRange']['end_date']}");
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