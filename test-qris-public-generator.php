<?php

/**
 * Test QRIS Public Generator
 * 
 * Test script untuk memverifikasi fungsi QRIS generator
 * 
 * Usage:
 * php test-qris-public-generator.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== QRIS Public Generator Test ===\n\n";

// Test 1: Check if route exists
echo "1. Testing route registration...\n";
try {
    $route = app('router')->getRoutes()->getByName('qris.public-generator');
    if ($route) {
        echo "   ✓ Route 'qris.public-generator' registered\n";
        echo "   URL: " . url('/qris-generator') . "\n";
    } else {
        echo "   ✗ Route not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check if Livewire component exists
echo "2. Testing Livewire component...\n";
try {
    if (class_exists('App\Livewire\QrisPublicGenerator')) {
        echo "   ✓ QrisPublicGenerator component exists\n";
        
        // Test component instantiation
        $component = new App\Livewire\QrisPublicGenerator();
        echo "   ✓ Component can be instantiated\n";
    } else {
        echo "   ✗ Component class not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check if view exists
echo "3. Testing view file...\n";
try {
    if (view()->exists('livewire.qris-public-generator')) {
        echo "   ✓ View 'livewire.qris-public-generator' exists\n";
    } else {
        echo "   ✗ View not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check if layout exists
echo "4. Testing layout file...\n";
try {
    if (view()->exists('layouts.public')) {
        echo "   ✓ Layout 'layouts.public' exists\n";
    } else {
        echo "   ✗ Layout not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Check storage directory
echo "5. Testing storage directory...\n";
$storagePath = storage_path('app/public/qris-generated');
if (is_dir($storagePath)) {
    echo "   ✓ Storage directory exists: $storagePath\n";
    if (is_writable($storagePath)) {
        echo "   ✓ Directory is writable\n";
    } else {
        echo "   ✗ Directory is not writable\n";
    }
} else {
    echo "   ✗ Storage directory not found\n";
    echo "   Creating directory...\n";
    mkdir($storagePath, 0755, true);
    echo "   ✓ Directory created\n";
}

echo "\n";

// Test 6: Check QrisStatic model
echo "6. Testing QrisStatic model...\n";
try {
    $count = App\Models\QrisStatic::where('is_active', true)->count();
    echo "   ✓ QrisStatic model accessible\n";
    echo "   Active QRIS count: $count\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Test QRIS generation logic
echo "7. Testing QRIS generation logic...\n";
try {
    // Sample static QRIS (simplified for testing)
    $staticQris = '00020101021126360014ID.CO.QRIS.WWW0118IDKU2024010100000303UMI51440014ID.CO.QRIS.WWW0118IDKU20240101000003020215021500000000000005802ID5912TEST MERCHANT6007JAKARTA61051234062070703A016304ABCD';
    
    // Test CRC16 calculation
    $component = new App\Livewire\QrisPublicGenerator();
    $reflection = new ReflectionClass($component);
    $method = $reflection->getMethod('crc16');
    $method->setAccessible(true);
    
    $testString = '00020101021126360014ID.CO.QRIS.WWW0118IDKU2024010100000303UMI51440014ID.CO.QRIS.WWW0118IDKU20240101000003020215021500000000000005802ID5912TEST MERCHANT6007JAKARTA61051234062070703A01';
    $crc = $method->invoke($component, $testString);
    
    echo "   ✓ CRC16 calculation works\n";
    echo "   Sample CRC: $crc\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 8: Check Endroid QR Code library
echo "8. Testing Endroid QR Code library...\n";
try {
    if (class_exists('Endroid\QrCode\Builder\Builder')) {
        echo "   ✓ Endroid QR Code library installed\n";
        
        // Test QR code generation
        $builder = new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\PngWriter,
            writerOptions: [],
            validateResult: false,
            data: 'TEST',
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            size: 100,
            margin: 10,
        );
        $result = $builder->build();
        echo "   ✓ QR code can be generated\n";
    } else {
        echo "   ✗ Endroid QR Code library not found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "All tests completed. Check results above.\n";
echo "\nTo access the page, visit:\n";
echo url('/qris-generator') . "\n";
echo "\nMake sure your development server is running:\n";
echo "php artisan serve\n";
