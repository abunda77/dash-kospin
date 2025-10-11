<?php

/**
 * Test Script untuk Makan Bergizi Gratis API
 * 
 * Cara menggunakan:
 * 1. Pastikan server Laravel sudah running (php artisan serve)
 * 2. Jalankan: php test-makan-bergizi-gratis.php
 */

// Configuration
$baseUrl = 'http://localhost:8000'; // Sesuaikan dengan URL Laravel Anda
$apiEndpoint = '/api/makan-bergizi-gratis';

// Test data - GANTI dengan no_tabungan yang valid dari database Anda
$testNoTabungan = '8888-5592'; // Sesuaikan dengan data di database

echo "===========================================\n";
echo "Test Makan Bergizi Gratis API\n";
echo "===========================================\n\n";

// Test 1: Valid Request (First time today)
echo "Test 1: Valid Request (First time today)\n";
echo "-------------------------------------------\n";

$data = [
    'no_tabungan' => $testNoTabungan
];

$ch = curl_init($baseUrl . $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

// Test 2: Duplicate Request (Should fail)
echo "Test 2: Duplicate Request (Should fail with 409)\n";
echo "-------------------------------------------\n";

$ch = curl_init($baseUrl . $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

// Test 3: Invalid No Tabungan
echo "Test 3: Invalid No Tabungan (Should fail with 422)\n";
echo "-------------------------------------------\n";

$invalidData = [
    'no_tabungan' => 'INVALID_NUMBER_12345'
];

$ch = curl_init($baseUrl . $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

// Test 4: Missing No Tabungan
echo "Test 4: Missing No Tabungan (Should fail with 422)\n";
echo "-------------------------------------------\n";

$emptyData = [];

$ch = curl_init($baseUrl . $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emptyData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

echo "===========================================\n";
echo "Test Selesai\n";
echo "===========================================\n";
