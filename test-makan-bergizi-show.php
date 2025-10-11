<?php

/**
 * Test script untuk API endpoint show Makan Bergizi Gratis
 * Menguji dengan ID numerik dan Hashids
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MakanBergizisGratis;
use App\Helpers\HashidsHelper;

echo "=== TEST MAKAN BERGIZI GRATIS SHOW API ===\n\n";

// Get sample record
$record = MakanBergizisGratis::with(['tabungan', 'profile'])->first();

if (!$record) {
    echo "❌ Tidak ada data di database. Jalankan dulu test-makan-bergizi-gratis.php untuk create data.\n";
    exit(1);
}

$numericId = $record->id;
$hashedId = HashidsHelper::encode($numericId);

echo "📊 Sample Data:\n";
echo "   - Numeric ID: {$numericId}\n";
echo "   - Hashed ID: {$hashedId}\n";
echo "   - No Tabungan: {$record->no_tabungan}\n";
echo "   - Tanggal: {$record->tanggal_pemberian->format('d/m/Y')}\n\n";

// Test 1: Show dengan Numeric ID
echo "🧪 Test 1: Show dengan Numeric ID\n";
echo "   GET /api/makan-bergizi-gratis/{$numericId}\n";

$response1 = testShowEndpoint($numericId);
echo "   Status: " . ($response1['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
if ($response1['success']) {
    echo "   Response ID: {$response1['data']['id']}\n";
    echo "   No Tabungan: {$response1['data']['no_tabungan']}\n";
}
echo "\n";

// Test 2: Show dengan Hashed ID
echo "🧪 Test 2: Show dengan Hashed ID\n";
echo "   GET /api/makan-bergizi-gratis/{$hashedId}\n";

$response2 = testShowEndpoint($hashedId);
echo "   Status: " . ($response2['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
if ($response2['success']) {
    echo "   Response ID: {$response2['data']['id']}\n";
    echo "   No Tabungan: {$response2['data']['no_tabungan']}\n";
}
echo "\n";

// Test 3: Show dengan Invalid ID
echo "🧪 Test 3: Show dengan Invalid ID\n";
echo "   GET /api/makan-bergizi-gratis/invalid-id-123\n";

$response3 = testShowEndpoint('invalid-id-123');
echo "   Status: " . ($response3['success'] ? '❌ SHOULD FAIL' : '✅ CORRECTLY FAILED') . "\n";
echo "   Message: {$response3['message']}\n";
echo "\n";

// Test 4: Show dengan Non-existent ID
echo "🧪 Test 4: Show dengan Non-existent ID\n";
echo "   GET /api/makan-bergizi-gratis/999999\n";

$response4 = testShowEndpoint(999999);
echo "   Status: " . ($response4['success'] ? '❌ SHOULD FAIL' : '✅ CORRECTLY FAILED') . "\n";
echo "   Message: {$response4['message']}\n";
echo "\n";

// Summary
echo "=== SUMMARY ===\n";
$passed = 0;
$failed = 0;

if ($response1['success']) $passed++; else $failed++;
if ($response2['success']) $passed++; else $failed++;
if (!$response3['success']) $passed++; else $failed++;
if (!$response4['success']) $passed++; else $failed++;

echo "✅ Passed: {$passed}/4\n";
echo "❌ Failed: {$failed}/4\n\n";

if ($failed === 0) {
    echo "🎉 All tests passed!\n";
} else {
    echo "⚠️  Some tests failed. Check the output above.\n";
}

/**
 * Helper function to test show endpoint
 */
function testShowEndpoint($id): array
{
    try {
        $controller = new App\Http\Controllers\Api\MakanBergizisGratisController();
        $response = $controller->show($id);
        
        if ($response instanceof Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            return [
                'success' => $response->status() === 200,
                'status_code' => $response->status(),
                'message' => $data['message'] ?? 'Success',
                'data' => $data['data'] ?? $data,
            ];
        }
        
        // If it's a Resource response
        if ($response instanceof App\Http\Resources\MakanBergizisGratisResource) {
            $data = json_decode($response->toJson(), true);
            return [
                'success' => true,
                'status_code' => 200,
                'message' => 'Success',
                'data' => $data['data'] ?? $data,
            ];
        }
        
        return [
            'success' => false,
            'status_code' => 500,
            'message' => 'Unknown response type',
            'data' => null,
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'status_code' => 500,
            'message' => $e->getMessage(),
            'data' => null,
        ];
    }
}
