<?php

namespace App\Http\Controllers;

use App\Models\Tabungan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class TabunganBarcodeController extends Controller
{
    public function printBarcode($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

        // Generate URL untuk scan barcode
        $scanUrl = route('tabungan.scan', $tabungan->id);
        $qrCodePath = null;
        $hasQrCode = false;
        $error = null;

        try {
            // Download QR Code dari online service dengan context options
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($scanUrl);

            // Set context options untuk file_get_contents
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'follow_location' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $qrCodeData = @file_get_contents($qrCodeUrl, false, $context);

            if ($qrCodeData !== false && strlen($qrCodeData) > 0) {
                // Save QR code to temporary file for DOMPDF (works better than base64)
                $tempDir = storage_path('app/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $qrCodePath = $tempDir . '/qr_' . $id . '_' . time() . '.png';
                file_put_contents($qrCodePath, $qrCodeData);
                $hasQrCode = true;

                Log::info('QR Code downloaded successfully', [
                    'tabungan_id' => $id,
                    'data_size' => strlen($qrCodeData),
                    'temp_path' => $qrCodePath
                ]);
            } else {
                throw new \Exception('Tidak dapat mengunduh QR Code dari server');
            }
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('QR Code generation failed', [
                'tabungan_id' => $id,
                'error' => $e->getMessage(),
                'url' => $qrCodeUrl ?? 'N/A'
            ]);

            $error = 'QR Code tidak dapat dimuat: ' . $e->getMessage();
        }

        // Generate PDF dengan atau tanpa QR Code
        $pdf = Pdf::loadView('pdf.tabungan-barcode', [
            'tabungan' => $tabungan,
            'qrCodePath' => $qrCodePath,
            'scanUrl' => $scanUrl,
            'hasQrCode' => $hasQrCode,
            'error' => $error
        ]);

        // Set paper size dan options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        $filename = 'barcode_tabungan_' . $tabungan->no_tabungan . '_' . date('Y-m-d_H-i-s') . '.pdf';

        $response = $pdf->download($filename);

        // Clean up temporary QR code file
        if ($hasQrCode && $qrCodePath && file_exists($qrCodePath)) {
            @unlink($qrCodePath);
        }

        return $response;
    }

    public function scan($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

        return view('tabungan.scan', compact('tabungan'));
    }

    public function testQrCode($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);
        $scanUrl = route('tabungan.scan', $tabungan->id);

        // Test QR code generation
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($scanUrl);

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'follow_location' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $qrCodeData = @file_get_contents($qrCodeUrl, false, $context);

        $debug = [
            'tabungan_id' => $id,
            'scan_url' => $scanUrl,
            'qr_api_url' => $qrCodeUrl,
            'qr_data_fetched' => $qrCodeData !== false,
            'qr_data_size' => $qrCodeData ? strlen($qrCodeData) : 0,
            'base64_preview' => $qrCodeData ? 'data:image/png;base64,' . base64_encode($qrCodeData) : null,
        ];

        return response()->json($debug);
    }
}
