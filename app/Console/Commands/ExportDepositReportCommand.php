<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deposito;
use App\Models\ProdukDeposito;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportDepositReportCommand extends Command
{
    protected $signature = 'report:export-deposit 
                            {--status=all : Status filter (all, active, ended, cancelled)}
                            {--jangka-waktu=all : Term filter (all, 1, 3, 6, 12, 24)}
                            {--start-date= : Start date (Y-m-d format)}
                            {--end-date= : End date (Y-m-d format)}
                            {--chunk-size=100 : Chunk size for processing}
                            {--memory-limit=1024 : Memory limit in MB}
                            {--public : Save to public storage for download link}';

    protected $description = 'Export deposit reports to PDF with progress tracking';

    private string $progressKey;
    private $progressBar;

    public function handle(): int
    {
        $this->info('🚀 Starting deposit PDF export process...');

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

            // Set memory limit
            ini_set('memory_limit', $options['memoryLimit'] . 'M');

            // Generate PDF
            $pdfPath = $this->generateDepositReport($options);

            // Handle public storage if requested
            $downloadUrl = $this->handlePublicStorage($pdfPath, $options);

            $this->displayResults($pdfPath, $downloadUrl, $options);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Export failed: {$e->getMessage()}");
            Log::error('Deposit PDF export command failed', [
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
        $status = $this->option('status');
        $jangkaWaktu = $this->option('jangka-waktu');
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');
        $chunkSize = (int) $this->option('chunk-size');
        $memoryLimit = (int) $this->option('memory-limit');
        $isPublic = $this->option('public');

        // Validate status
        if (!in_array($status, ['all', 'active', 'ended', 'cancelled'])) {
            throw new \InvalidArgumentException('Invalid status. Use: all, active, ended, or cancelled');
        }

        // Validate jangka waktu
        if (!in_array($jangkaWaktu, ['all', '1', '3', '6', '12', '24'])) {
            throw new \InvalidArgumentException('Invalid jangka waktu. Use: all, 1, 3, 6, 12, or 24');
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
        } elseif (!$startDate && !$endDate) {
            // Default to current month if no dates provided
            $dateRange = [
                'start' => now()->startOfMonth()->format('Y-m-d'),
                'end' => now()->endOfMonth()->format('Y-m-d')
            ];
        }

        return [
            'status' => $status,
            'jangkaWaktu' => $jangkaWaktu,
            'dateRange' => $dateRange,
            'chunkSize' => max(10, $chunkSize),
            'memoryLimit' => max(256, $memoryLimit),
            'isPublic' => $isPublic
        ];
    }

    private function generateDepositReport(array $options): string
    {
        $this->info("📊 Generating deposit report PDF...");

        // Build base query
        $query = $this->buildQuery($options);
        
        // Get total count for progress tracking
        $total = $query->count();
        $this->updateProgress(0, $total, 0);

        $this->info("📈 Processing {$total} deposit records...");

        // Process data in chunks for better memory management
        $allData = collect();
        $processed = 0;

        $query->chunk($options['chunkSize'], function ($deposits) use (&$allData, &$processed, $total) {
            $allData = $allData->merge($deposits);
            $processed += $deposits->count();
            
            $percent = $total > 0 ? ($processed / $total) * 100 : 100;
            $this->updateProgress($processed, $total, $percent);
        });

        // Calculate statistics
        $stats = $this->calculateStats($allData);

        // Generate PDF
        $pdf = Pdf::loadView('reports.laporan-deposito', [
            'data' => $allData,
            'stats' => $stats,
            'filters' => [
                'tanggal_mulai' => $options['dateRange']['start'] ?? null,
                'tanggal_akhir' => $options['dateRange']['end'] ?? null,
                'status' => $options['status'],
                'jangka_waktu' => $options['jangkaWaktu'],
            ]
        ]);

        return $this->savePdf($pdf, 'laporan-deposito', $options);
    }

    private function buildQuery(array $options)
    {
        $query = Deposito::query()->with('profile');

        // Apply date range filter
        if (!empty($options['dateRange'])) {
            $query->whereBetween('tanggal_pembukaan', [
                Carbon::parse($options['dateRange']['start'])->startOfDay(),
                Carbon::parse($options['dateRange']['end'])->endOfDay()
            ]);
        }

        // Apply status filter
        if ($options['status'] !== 'all') {
            $query->where('status', $options['status']);
        }

        // Apply jangka waktu filter
        if ($options['jangkaWaktu'] !== 'all') {
            $query->where('jangka_waktu', $options['jangkaWaktu']);
        }

        return $query;
    }

    private function calculateStats($data): array
    {
        return [
            'total_deposito' => $data->count(),
            'total_nominal' => $data->sum('nominal_penempatan'),
            'total_bunga' => $data->sum('nominal_bunga'),
            'rata_rata_nominal' => $data->avg('nominal_penempatan'),
        ];
    }

    private function savePdf($pdf, string $prefix, array $options): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        
        $statusName = $options['status'] === 'all' ? 'semua-status' : $options['status'];
        $termName = $options['jangkaWaktu'] === 'all' ? 'semua-jangka-waktu' : $options['jangkaWaktu'] . '-bulan';

        $filename = "{$prefix}-{$statusName}-{$termName}-{$timestamp}.pdf";
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
        $this->info('✅ Deposit PDF export completed successfully!');
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
        $this->line("   Type: Deposit Report");
        $this->line("   Status: " . ($options['status'] === 'all' ? 'All Status' : ucfirst($options['status'])));
        $this->line("   Term: " . ($options['jangkaWaktu'] === 'all' ? 'All Terms' : $options['jangkaWaktu'] . ' Months'));

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