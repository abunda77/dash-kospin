<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Zxing\QrReader;

class QrisHelper
{
    /**
     * Read QRIS string from uploaded image using local QR decoder
     * Handles both temporary upload path and stored file path
     */
    public static function readQrisFromImage(string $imagePath): ?string
    {
        try {
            // Determine the actual file path
            $fullPath = null;

            // Check if it's a temporary upload path (Windows temp file)
            if (file_exists($imagePath)) {
                $fullPath = $imagePath;
                \Log::info('Using temporary file path: '.$fullPath);
            }
            // Check if it's a stored file path
            elseif (Storage::disk('public')->exists($imagePath)) {
                $fullPath = Storage::disk('public')->path($imagePath);
                \Log::info('Using stored file path: '.$fullPath);
            }
            // Try to construct path from storage
            else {
                $storagePath = Storage::disk('public')->path($imagePath);
                if (file_exists($storagePath)) {
                    $fullPath = $storagePath;
                    \Log::info('Using constructed storage path: '.$fullPath);
                }
            }

            if (! $fullPath || ! file_exists($fullPath)) {
                \Log::error('QRIS image file not found. Tried path: '.$imagePath);

                return null;
            }

            // Use local QR code reader library
            $qrcode = new QrReader($fullPath);
            $qrisString = $qrcode->text();

            if ($qrisString && self::isValidQris($qrisString)) {
                \Log::info('QRIS successfully extracted: '.substr($qrisString, 0, 50).'...');

                return $qrisString;
            } else {
                \Log::warning('No valid QRIS found in image or QR code could not be read');

                return null;
            }
        } catch (\Exception $e) {
            \Log::error('Error reading QRIS from image: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());

            return null;
        }
    }

    /**
     * Validate if string is a valid QRIS
     */
    public static function isValidQris(string $qrisString): bool
    {
        // Basic validation: QRIS should contain specific tags
        return strlen($qrisString) > 50
            && str_contains($qrisString, '5802ID')
            && str_contains($qrisString, '0002');
    }

    /**
     * Parse merchant name from QRIS string
     */
    public static function parseMerchantName(string $qrisData): string
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
}
