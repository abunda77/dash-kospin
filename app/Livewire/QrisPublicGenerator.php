<?php

namespace App\Livewire;

use App\Models\QrisStatic;
use Livewire\Component;

class QrisPublicGenerator extends Component
{
    public $saved_qris = '';
    public $static_qris = '';
    public $amount = '';
    public $fee_type = 'Rupiah';
    public $fee_value = '0';
    
    public $dynamicQris = null;
    public $merchantName = null;
    public $qrImageUrl = null;
    public $errorMessage = null;

    protected $rules = [
        'static_qris' => 'required|string|min:10',
        'amount' => 'required|numeric|min:1',
        'fee_type' => 'required|in:Rupiah,Persentase',
        'fee_value' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'static_qris.required' => 'QRIS code harus diisi',
        'static_qris.min' => 'QRIS code tidak valid',
        'amount.required' => 'Jumlah harus diisi',
        'amount.numeric' => 'Jumlah harus berupa angka',
        'amount.min' => 'Jumlah minimal Rp 1',
    ];

    public function mount()
    {
        // Initialize default values
        $this->fee_type = 'Rupiah';
        $this->fee_value = '0';
    }

    public function updatedSavedQris($value)
    {
        if ($value) {
            $qris = QrisStatic::where('is_active', true)->find($value);
            if ($qris) {
                $this->static_qris = $qris->qris_string;
            }
        }
    }

    public function generate()
    {
        $this->validate();

        try {
            $this->errorMessage = null;
            
            $this->merchantName = $this->parseMerchantName($this->static_qris);
            $this->dynamicQris = $this->generateDynamicQris(
                $this->static_qris,
                $this->amount,
                $this->fee_type,
                $this->fee_value ?? '0'
            );

            // Generate QR code image
            $this->generateQrImage();

            session()->flash('success', 'QRIS Dinamis berhasil dibuat!');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: ' . $e->getMessage();
            $this->dynamicQris = null;
            $this->qrImageUrl = null;
        }
    }

    protected function generateQrImage(): void
    {
        if (!$this->dynamicQris) {
            return;
        }

        try {
            $builder = new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\PngWriter,
                writerOptions: [],
                validateResult: false,
                data: $this->dynamicQris,
                encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
                size: 400,
                margin: 10,
            );

            $result = $builder->build();

            // Save to storage
            $filename = 'qris-public-' . now()->format('YmdHis') . '-' . uniqid() . '.png';
            \Storage::disk('public')->put('qris-generated/' . $filename, $result->getString());

            $this->qrImageUrl = asset('storage/qris-generated/' . $filename);

            \Log::info('Public QR code generated: ' . $filename);
        } catch (\Exception $e) {
            \Log::error('Error generating public QR image: ' . $e->getMessage());
            throw $e;
        }
    }

    public function resetForm()
    {
        $this->reset([
            'saved_qris',
            'static_qris',
            'amount',
            'fee_type',
            'fee_value',
            'dynamicQris',
            'merchantName',
            'qrImageUrl',
            'errorMessage'
        ]);
        
        $this->fee_type = 'Rupiah';
        $this->fee_value = '0';
        
        session()->flash('info', 'Form telah direset');
    }

    protected function parseMerchantName(string $qrisData): string
    {
        $tag = '59';
        $tagIndex = strpos($qrisData, $tag);

        if ($tagIndex === false) {
            return 'Merchant';
        }

        try {
            $lengthIndex = $tagIndex + strlen($tag);
            $lengthStr = substr($qrisData, $lengthIndex, 2);
            $length = intval($lengthStr);

            if ($length <= 0) {
                return 'Merchant';
            }

            $valueIndex = $lengthIndex + 2;
            $merchantName = substr($qrisData, $valueIndex, $length);

            return trim($merchantName) ?: 'Merchant';
        } catch (\Exception $e) {
            return 'Merchant';
        }
    }

    protected function generateDynamicQris(
        string $staticQris,
        string $amount,
        string $feeType,
        string $feeValue
    ): string {
        if (strlen($staticQris) < 4) {
            throw new \Exception('Data QRIS static tidak valid.');
        }

        // Remove CRC (last 4 characters)
        $qrisWithoutCrc = substr($staticQris, 0, -4);

        // Change from static (01) to dynamic (12)
        $step1 = str_replace('010211', '010212', $qrisWithoutCrc);

        // Split by merchant country code
        $parts = explode('5802ID', $step1);
        if (count($parts) !== 2) {
            throw new \Exception("Format QRIS tidak sesuai (missing '5802ID').");
        }

        // Build amount tag
        $amountStr = strval(intval($amount));
        $amountTag = '54' . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;

        // Build fee tag if applicable
        $feeTag = '';
        if ($feeValue && floatval($feeValue) > 0) {
            if ($feeType === 'Rupiah') {
                $feeValueStr = strval(intval($feeValue));
                $feeTag = '55020256' . str_pad(strlen($feeValueStr), 2, '0', STR_PAD_LEFT) . $feeValueStr;
            } else {
                $feeTag = '55020357' . str_pad(strlen($feeValue), 2, '0', STR_PAD_LEFT) . $feeValue;
            }
        }

        // Reconstruct payload
        $payload = $parts[0] . $amountTag . $feeTag . '5802ID' . $parts[1];

        // Calculate and append CRC
        $finalCrc = $this->crc16($payload);

        return $payload . $finalCrc;
    }

    protected function crc16(string $str): string
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);

        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord($str[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }

        $hex = strtoupper(dechex($crc & 0xFFFF));

        return str_pad($hex, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $savedQrisList = QrisStatic::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.qris-public-generator', [
            'savedQrisList' => $savedQrisList
        ])->layout('layouts.public');
    }
}
