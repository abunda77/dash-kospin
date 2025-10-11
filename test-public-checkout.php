<?php

/**
 * Test Script for Makan Bergizi Gratis Public Checkout Page
 * 
 * Usage: php test-public-checkout.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tabungan;
use App\Models\MakanBergizisGratis;
use App\Helpers\HashidsHelper;

echo "=== Makan Bergizi Gratis Public Checkout Test ===\n\n";

// Test 1: Get sample tabungan for testing
echo "1. Getting sample tabungan...\n";
$tabungan = Tabungan::with(['profile', 'produkTabungan'])->first();

if (!$tabungan) {
    echo "   ❌ No tabungan found in database\n";
    exit(1);
}

echo "   ✓ Found tabungan: {$tabungan->no_tabungan}\n";
echo "   ✓ Nasabah: {$tabungan->profile->first_name} {$tabungan->profile->last_name}\n\n";

// Test 2: Generate hash for QR code
echo "2. Generating hash for QR code...\n";
$hash = HashidsHelper::encode($tabungan->id);
echo "   ✓ Hash: {$hash}\n\n";

// Test 3: Generate URLs
echo "3. Generating URLs...\n";
$baseUrl = config('app.url');
$manualUrl = "{$baseUrl}/makan-bergizi-gratis";
$qrUrl = "{$baseUrl}/makan-bergizi-gratis/{$hash}";

echo "   Manual Entry URL:\n";
echo "   {$manualUrl}\n\n";
echo "   QR Code Scan URL:\n";
echo "   {$qrUrl}\n\n";

// Test 4: Check if already checked out today
echo "4. Checking checkout status for today...\n";
$alreadyCheckedOut = MakanBergizisGratis::existsForToday($tabungan->no_tabungan);

if ($alreadyCheckedOut) {
    echo "   ⚠️  Already checked out today\n";
    
    $record = MakanBergizisGratis::where('no_tabungan', $tabungan->no_tabungan)
        ->whereDate('tanggal_pemberian', today())
        ->first();
    
    if ($record) {
        echo "   Record ID: {$record->id}\n";
        echo "   Scanned at: {$record->scanned_at->format('Y-m-d H:i:s')}\n";
    }
} else {
    echo "   ✓ Available for checkout\n";
}
echo "\n";

// Test 5: Get checkout statistics
echo "5. Checkout Statistics...\n";
$todayCount = MakanBergizisGratis::whereDate('tanggal_pemberian', today())->count();
$totalCount = MakanBergizisGratis::count();

echo "   Today's checkouts: {$todayCount}\n";
echo "   Total checkouts: {$totalCount}\n\n";

// Test 6: Test hash decode
echo "6. Testing hash decode...\n";
$decodedId = HashidsHelper::decode($hash);

if ($decodedId === $tabungan->id) {
    echo "   ✓ Hash decode successful\n";
    echo "   Original ID: {$tabungan->id}\n";
    echo "   Decoded ID: {$decodedId}\n";
} else {
    echo "   ❌ Hash decode failed\n";
}
echo "\n";

// Test 7: Simulate checkout (dry run)
echo "7. Simulating checkout (dry run)...\n";

if (!$alreadyCheckedOut) {
    echo "   Would create record with:\n";
    echo "   - Tabungan ID: {$tabungan->id}\n";
    echo "   - Profile ID: {$tabungan->profile->id_user}\n";
    echo "   - No. Tabungan: {$tabungan->no_tabungan}\n";
    echo "   - Tanggal: " . today()->format('Y-m-d') . "\n";
    echo "   - Saldo: " . format_rupiah($tabungan->saldo) . "\n";
    echo "\n";
    echo "   ✓ Checkout would be successful\n";
} else {
    echo "   ⚠️  Cannot checkout - already checked out today\n";
}
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "✓ All tests completed\n\n";

echo "Next Steps:\n";
echo "1. Open browser and visit:\n";
echo "   {$manualUrl}\n\n";
echo "2. Test manual entry with:\n";
echo "   No. Tabungan: {$tabungan->no_tabungan}\n\n";
echo "3. Test QR scan by visiting:\n";
echo "   {$qrUrl}\n\n";
echo "4. Generate QR code from Filament:\n";
echo "   Admin > Tabungan > View > Print Barcode\n\n";

// Additional test data
echo "=== Additional Test Data ===\n";
$sampleTabungans = Tabungan::with('profile')->take(5)->get();

echo "Sample tabungan numbers for testing:\n";
foreach ($sampleTabungans as $t) {
    $checkedOut = MakanBergizisGratis::existsForToday($t->no_tabungan);
    $status = $checkedOut ? '✓ Checked out' : '○ Available';
    echo "  {$status} - {$t->no_tabungan} ({$t->profile->first_name} {$t->profile->last_name})\n";
}
echo "\n";

echo "Done!\n";
