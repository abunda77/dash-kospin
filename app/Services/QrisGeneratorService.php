<?php

namespace App\Services;

use App\Models\QrisStatic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QrisGeneratorService
{
    public function generate(QrisStatic $staticQris, int $jumlahBayar): array
    {
        $payload = $this->buildDynamicQris($staticQris->qris_string, $jumlahBayar, 'Rupiah', 0);
        $filename = $this->saveQrImage($payload);

        return [
            'payload' => $payload,
            'image_path' => $filename ? 'qris-generated/'.$filename : null,
        ];
    }

    public function buildDynamicQris(
        string $staticQris,
        string $amount,
        string $feeType,
        string $feeValue
    ): string {
        if (strlen($staticQris) < 4) {
            throw new \Exception('Data QRIS statis tidak valid.');
        }

        $qrisWithoutCrc = substr($staticQris, 0, -4);
        $step1 = str_replace('010211', '010212', $qrisWithoutCrc);
        $parts = explode('5802ID', $step1);

        if (count($parts) !== 2) {
            throw new \Exception("Format QRIS tidak sesuai (tidak ditemukan '5802ID').");
        }

        $amountStr = strval(intval($amount));
        $amountTag = '54'.str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT).$amountStr;

        $feeTag = '';
        if ($feeValue && floatval($feeValue) > 0) {
            if ($feeType === 'Rupiah') {
                $feeValueStr = strval(intval($feeValue));
                $feeTag = '55020256'.str_pad(strlen($feeValueStr), 2, '0', STR_PAD_LEFT).$feeValueStr;
            } else {
                $feeTag = '55020357'.str_pad(strlen($feeValue), 2, '0', STR_PAD_LEFT).$feeValue;
            }
        }

        $payload = $parts[0].$amountTag.$feeTag.'5802ID'.$parts[1];

        return $payload.$this->crc16($payload);
    }

    private function crc16(string $str): string
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

        return str_pad(strtoupper(dechex($crc & 0xFFFF)), 4, '0', STR_PAD_LEFT);
    }

    private function saveQrImage(string $qrisData): ?string
    {
        try {
            $builder = new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\PngWriter,
                writerOptions: [],
                validateResult: false,
                data: $qrisData,
                encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
                size: 400,
                margin: 10,
            );

            $result = $builder->build();
            $filename = 'qris-dynamic-'.now()->format('YmdHis').'-'.uniqid().'.png';

            Storage::disk('public')->put('qris-generated/'.$filename, $result->getString());

            return $filename;
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan QR image: '.$e->getMessage());

            return null;
        }
    }
}
