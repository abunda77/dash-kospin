<?php

namespace App\Http\Controllers;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\BarcodeScanLog;
use App\Helpers\HashidsHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class TabunganBarcodeController extends Controller
{
    public function printBarcode($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

        // Generate QR Code dengan data no_tabungan (bukan URL)
        $qrData = $tabungan->no_tabungan;
        $qrCodePath = null;
        $hasQrCode = false;
        $error = null;

        try {
            // Download QR Code dari online service dengan no_tabungan sebagai data
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);

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
                    'no_tabungan' => $qrData,
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
                'no_tabungan' => $qrData,
                'error' => $e->getMessage(),
                'url' => $qrCodeUrl ?? 'N/A'
            ]);

            $error = 'QR Code tidak dapat dimuat: ' . $e->getMessage();
        }

        // Generate PDF dengan atau tanpa QR Code
        $pdf = Pdf::loadView('pdf.tabungan-barcode', [
            'tabungan' => $tabungan,
            'qrCodePath' => $qrCodePath,
            'qrData' => $qrData,
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

    public function scan($hash, Request $request)
    {
        // Decode hash to get real ID
        $id = HashidsHelper::decode($hash);

        if ($id === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired barcode',
                'error' => 'INVALID_HASH'
            ], 404);
        }

        try {
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

            // Ambil transaksi terakhir
            $transaksiTerakhir = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->with('admin')
                ->orderBy('tanggal_transaksi', 'desc')
                ->first();

            // Log the scan activity
            $this->logScan($tabungan->id, $hash, $request);

            // Format response untuk React frontend
            $responseData = [
                'success' => true,
                'message' => 'Tabungan data retrieved successfully',
                'data' => [
                    'rekening' => [
                        'no_tabungan' => $tabungan->no_tabungan,
                        'produk' => $tabungan->produkTabungan->nama_produk ?? 'N/A',
                        'saldo' => $tabungan->saldo,
                        'saldo_formatted' => format_rupiah($tabungan->saldo),
                        'status' => $tabungan->status_rekening,
                        'tanggal_buka' => $tabungan->tanggal_buka?->format('d/m/Y'),
                        'tanggal_buka_iso' => $tabungan->tanggal_buka?->toISOString(),
                    ],
                    'nasabah' => [
                        'nama_lengkap' => $tabungan->profile->first_name . ' ' . $tabungan->profile->last_name,
                        'first_name' => $tabungan->profile->first_name,
                        'last_name' => $tabungan->profile->last_name,
                        'phone' => $tabungan->profile->phone,
                        'email' => $tabungan->profile->email,
                        'whatsapp' => $tabungan->profile->whatsapp,
                        'address' => $tabungan->profile->address,
                    ],
                    'produk_detail' => [
                        'id' => $tabungan->produkTabungan->id ?? null,
                        'nama' => $tabungan->produkTabungan->nama_produk ?? 'N/A',
                        'keterangan' => $tabungan->produkTabungan->keterangan ?? null,
                    ],
                    'transaksi_terakhir' => $transaksiTerakhir ? [
                        'kode_transaksi' => $transaksiTerakhir->kode_transaksi,
                        'jenis_transaksi' => $transaksiTerakhir->jenis_transaksi,
                        'jenis_transaksi_label' => $transaksiTerakhir->jenis_transaksi === TransaksiTabungan::JENIS_SETORAN ? 'Setoran' : 'Penarikan',
                        'jumlah' => $transaksiTerakhir->jumlah,
                        'jumlah_formatted' => format_rupiah($transaksiTerakhir->jumlah),
                        'tanggal_transaksi' => $transaksiTerakhir->tanggal_transaksi->format('d/m/Y H:i:s'),
                        'tanggal_transaksi_iso' => $transaksiTerakhir->tanggal_transaksi->toISOString(),
                        'keterangan' => $transaksiTerakhir->keterangan,
                        'teller' => $transaksiTerakhir->admin?->name ?? 'N/A',
                    ] : null,
                    'metadata' => [
                        'scanned_at' => now()->toISOString(),
                        'scanned_at_formatted' => now()->format('d/m/Y H:i:s'),
                    ]
                ]
            ];

            // Send webhook notification
            $this->sendWebhookNotification($responseData, $hash, $request);

            return response()->json($responseData, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tabungan not found',
                'error' => 'NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error scanning barcode', [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving data',
                'error' => 'SERVER_ERROR'
            ], 500);
        }
    }

    public function testQrCode($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

        // Generate encoded URL
        $encodedId = HashidsHelper::encode($tabungan->id);
        $scanUrl = route('tabungan.scan', $encodedId);

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
            'encoded_id' => $encodedId,
            'scan_url' => $scanUrl,
            'qr_api_url' => $qrCodeUrl,
            'qr_data_fetched' => $qrCodeData !== false,
            'qr_data_size' => $qrCodeData ? strlen($qrCodeData) : 0,
            'base64_preview' => $qrCodeData ? 'data:image/png;base64,' . base64_encode($qrCodeData) : null,
            'security_note' => 'ID is now encoded using Hashids for security'
        ];

        return response()->json($debug);
    }

    /**
     * Log barcode scan activity
     */
    private function logScan(int $tabunganId, string $hash, Request $request): void
    {
        try {
            BarcodeScanLog::create([
                'tabungan_id' => $tabunganId,
                'hash' => $hash,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'is_mobile' => $this->isMobile($request->userAgent()),
                'scanned_at' => now(),
            ]);

            Log::info('Barcode scanned', [
                'tabungan_id' => $tabunganId,
                'hash' => $hash,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            // Don't fail the scan if logging fails
            Log::error('Failed to log barcode scan', [
                'error' => $e->getMessage(),
                'tabungan_id' => $tabunganId
            ]);
        }
    }

    /**
     * Detect if request is from mobile device
     */
    private function isMobile(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        $mobileKeywords = [
            'Mobile',
            'Android',
            'iPhone',
            'iPad',
            'iPod',
            'BlackBerry',
            'Windows Phone',
            'Opera Mini'
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send webhook notification with scan data
     */
    private function sendWebhookNotification(array $responseData, string $hash, Request $request): void
    {
        $webhookUrl = env('WEBHOOK_URL_BARCODE_TABUNGAN');

        if (!$webhookUrl) {
            Log::warning('Webhook URL not configured', [
                'env_var' => 'WEBHOOK_URL_BARCODE_TABUNGAN'
            ]);
            return;
        }

        try {
            // Prepare webhook payload
            $payload = [
                'event' => 'barcode_scanned',
                'timestamp' => now()->toISOString(),
                'scan_data' => [
                    'hash' => $hash,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->header('referer'),
                    'is_mobile' => $this->isMobile($request->userAgent()),
                ],
                'tabungan_data' => $responseData['data']
            ];

            // Send webhook request with timeout
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Kospin-Tabungan-Webhook/1.0'
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Webhook notification sent successfully', [
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->status(),
                    'tabungan_id' => $responseData['data']['rekening']['no_tabungan'] ?? null,
                    'hash' => $hash
                ]);
            } else {
                Log::error('Webhook notification failed', [
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'tabungan_id' => $responseData['data']['rekening']['no_tabungan'] ?? null,
                    'hash' => $hash
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send webhook notification', [
                'webhook_url' => $webhookUrl,
                'error' => $e->getMessage(),
                'tabungan_id' => $responseData['data']['rekening']['no_tabungan'] ?? null,
                'hash' => $hash
            ]);
        }
    }
}
