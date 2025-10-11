<?php

/**
 * Test script for Webhook Barcode Tabungan
 * 
 * This script simulates the webhook payload that will be sent
 * when a Makan Bergizi Gratis checkout occurs.
 * 
 * Usage:
 * 1. Set your webhook URL below
 * 2. Run: php test-webhook-barcode.php
 */

// Configuration
$webhookUrl = 'https://webhook.site/your-unique-id'; // Change this to your test URL

// Sample payload matching the actual webhook structure
$payload = [
    'event' => 'makan_bergizi_gratis.checkout',
    'timestamp' => date('c'), // ISO 8601 format
    'data' => [
        'id' => 999,
        'no_tabungan' => 'TEST001',
        'tanggal_pemberian' => date('Y-m-d'),
        'scanned_at' => date('c'),
        'rekening' => [
            'no_tabungan' => 'TEST001',
            'produk' => 'Tabungan Mitra Sinara',
            'saldo' => 1500000,
            'saldo_formatted' => 'Rp 1.500.000',
            'status' => 'aktif',
            'tanggal_buka' => '01/01/2025',
            'tanggal_buka_iso' => '2025-01-01T00:00:00.000000Z',
        ],
        'nasabah' => [
            'nama_lengkap' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '081234567890',
            'email' => 'test@example.com',
            'whatsapp' => '081234567890',
            'address' => 'Jl. Test No. 123, Jakarta',
        ],
        'produk' => [
            'id' => 1,
            'nama' => 'Tabungan Mitra Sinara',
            'keterangan' => 'Produk tabungan reguler untuk anggota',
        ],
        'transaksi_terakhir' => [
            'kode_transaksi' => 'TRX-TEST-001',
            'jenis_transaksi' => 'setoran',
            'jenis_transaksi_label' => 'Setoran',
            'jumlah' => 100000,
            'jumlah_formatted' => 'Rp 100.000',
            'tanggal_transaksi' => date('d/m/Y H:i:s'),
            'tanggal_transaksi_iso' => date('c'),
            'keterangan' => 'Setoran tunai',
            'teller' => 'Admin Test',
        ],
    ],
];

echo "=== Webhook Barcode Tabungan Test ===\n\n";
echo "Target URL: {$webhookUrl}\n\n";
echo "Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Initialize cURL
$ch = curl_init($webhookUrl);

// Set cURL options
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
]);

echo "Sending webhook...\n";

// Execute request
$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

// Get response info
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$duration = round(($endTime - $startTime) * 1000, 2);

curl_close($ch);

// Display results
echo "\n=== Response ===\n\n";

if ($error) {
    echo "❌ Error: {$error}\n";
} else {
    echo "✓ HTTP Status: {$httpCode}\n";
    echo "✓ Duration: {$duration}ms\n\n";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✓ Success! Webhook received successfully.\n\n";
    } else {
        echo "⚠ Warning: Non-2xx status code received.\n\n";
    }
    
    echo "Response Body:\n";
    echo $response . "\n";
}

echo "\n=== Test Complete ===\n";
