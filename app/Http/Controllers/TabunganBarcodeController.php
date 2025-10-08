<?php

namespace App\Http\Controllers;

use App\Models\Tabungan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TabunganBarcodeController extends Controller
{
    public function printBarcode($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

        // Generate URL untuk scan barcode
        $scanUrl = route('tabungan.scan', $tabungan->id);

        try {
            // Download QR Code dari online service dan simpan sebagai base64
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($scanUrl);
            $qrCodeData = file_get_contents($qrCodeUrl);

            if ($qrCodeData !== false) {
                $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodeData);

                $pdf = Pdf::loadView('pdf.tabungan-barcode', [
                    'tabungan' => $tabungan,
                    'qrCodeBase64' => $qrCodeBase64,
                    'scanUrl' => $scanUrl,
                    'hasQrCode' => true
                ]);
            } else {
                throw new \Exception('Tidak dapat mengunduh QR Code');
            }
        } catch (\Exception $e) {
            // Fallback: PDF tanpa QR Code
            $pdf = Pdf::loadView('pdf.tabungan-barcode', [
                'tabungan' => $tabungan,
                'scanUrl' => $scanUrl,
                'hasQrCode' => false,
                'error' => 'QR Code tidak dapat dimuat: ' . $e->getMessage()
            ]);
        }

        $filename = 'barcode_tabungan_' . $tabungan->no_tabungan . '_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    public function scan($id)
    {
        $tabungan = Tabungan::with(['profile', 'produkTabungan'])->findOrFail($id);

        return view('tabungan.scan', compact('tabungan'));
    }
}
