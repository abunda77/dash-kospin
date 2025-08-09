<?php

/**
 * Simple test script to verify PDF export functionality
 * Run with: php test-export.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing PDF Export Commands\n";
echo "================================\n\n";

// Test 1: Check if commands are registered
echo "1. Checking command registration...\n";
try {
    $commands = Artisan::all();
    $exportCommands = array_filter(array_keys($commands), function($cmd) {
        return str_starts_with($cmd, 'report:');
    });
    
    echo "   ✅ Found commands: " . implode(', ', $exportCommands) . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Error checking commands: " . $e->getMessage() . "\n\n";
}

// Test 2: Check storage directories
echo "2. Checking storage directories...\n";
$tempDir = storage_path('app/temp');
$publicDir = storage_path('app/public/reports');

if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
    echo "   ✅ Created temp directory: $tempDir\n";
} else {
    echo "   ✅ Temp directory exists: $tempDir\n";
}

if (!is_dir($publicDir)) {
    mkdir($publicDir, 0755, true);
    echo "   ✅ Created public reports directory: $publicDir\n";
} else {
    echo "   ✅ Public reports directory exists: $publicDir\n";
}

// Test 3: Check if storage link exists
echo "\n3. Checking storage link...\n";
$storageLink = public_path('storage');
if (is_link($storageLink)) {
    echo "   ✅ Storage link exists\n";
} else {
    echo "   ⚠️  Storage link missing. Run: php artisan storage:link\n";
}

// Test 4: Check required services
echo "\n4. Checking required services...\n";
try {
    $exportService = app(\App\Services\LoanReportExportService::class);
    echo "   ✅ LoanReportExportService can be instantiated\n";
} catch (Exception $e) {
    echo "   ❌ Error with LoanReportExportService: " . $e->getMessage() . "\n";
}

try {
    $reportService = app(\App\Services\LoanReportService::class);
    echo "   ✅ LoanReportService can be instantiated\n";
} catch (Exception $e) {
    echo "   ❌ Error with LoanReportService: " . $e->getMessage() . "\n";
}

// Test 5: Check database connection
echo "\n5. Checking database connection...\n";
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 6: Check cache functionality
echo "\n6. Checking cache functionality...\n";
try {
    $testKey = 'test_progress_' . uniqid();
    \Illuminate\Support\Facades\Cache::put($testKey, ['test' => 'data'], 60);
    $retrieved = \Illuminate\Support\Facades\Cache::get($testKey);
    
    if ($retrieved && isset($retrieved['test'])) {
        echo "   ✅ Cache is working\n";
        \Illuminate\Support\Facades\Cache::forget($testKey);
    } else {
        echo "   ❌ Cache is not working properly\n";
    }
} catch (Exception $e) {
    echo "   ❌ Cache error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test Summary\n";
echo "===============\n";
echo "If all tests passed, you can now use the export commands:\n\n";
echo "📊 Export Commands:\n";
echo "   php artisan report:export-loan\n";
echo "   php artisan report:export-loan --type=transaction\n";
echo "   php artisan report:export-loan --public\n\n";
echo "🔍 Progress Commands:\n";
echo "   php artisan report:check-progress <key>\n\n";
echo "🧹 Cleanup Commands:\n";
echo "   php artisan report:cleanup\n";
echo "   php artisan report:cleanup --dry-run\n\n";
echo "🌐 Web Interface:\n";
echo "   Visit: http://your-domain.com/export-monitor\n\n";
echo "📚 Documentation:\n";
echo "   See: EXPORT_COMMANDS.md\n\n";