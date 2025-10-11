<?php
/**
 * Test Script untuk QR Barcode Tabungan
 * Simulasi scanning QR code dan testing API Makan Bergizi Gratis
 */

// Konfigurasi
$baseUrl = 'http://localhost:8000'; // Sesuaikan dengan URL aplikasi Anda
$apiUrl = $baseUrl . '/api/makan-bergizi-gratis';

// Simulasi data yang dibaca dari QR Code
$qrCodeData = '8888-5592'; // Ganti dengan no_tabungan yang valid

echo "=== TEST QR BARCODE TABUNGAN ===\n\n";
echo "QR Code Data: {$qrCodeData}\n";
echo "API Endpoint: {$apiUrl}\n\n";

// Test 1: Check if record exists for today
echo "--- Test 1: Check Today ---\n";
$checkUrl = $apiUrl . '/check-today';
$checkData = ['no_tabungan' => $qrCodeData];

$ch = curl_init($checkUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response:\n";
print_r(json_decode($response, true));
echo "\n";

// Test 2: Store new record (jika belum ada)
$responseData = json_decode($response, true);
if (isset($responseData['data']['exists']) && !$responseData['data']['exists']) {
    echo "--- Test 2: Store Record ---\n";
    $storeData = ['no_tabungan' => $qrCodeData];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($storeData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: {$httpCode}\n";
    echo "Response:\n";
    print_r(json_decode($response, true));
    echo "\n";
} else {
    echo "--- Test 2: Skipped (Record already exists today) ---\n\n";
}

// Test 3: Get list of records
echo "--- Test 3: Get Records ---\n";
$listUrl = $apiUrl . '?per_page=5';

$ch = curl_init($listUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response:\n";
$listData = json_decode($response, true);
if (isset($listData['data'])) {
    echo "Total Records: " . count($listData['data']) . "\n";
    foreach ($listData['data'] as $record) {
        echo "  - ID: {$record['id']}, No Tabungan: {$record['no_tabungan']}, Tanggal: {$record['tanggal_pemberian']}\n";
    }
} else {
    print_r($listData);
}
echo "\n";

echo "=== TEST SELESAI ===\n";
