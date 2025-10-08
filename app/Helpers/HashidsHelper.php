<?php

namespace App\Helpers;

use Hashids\Hashids;

class HashidsHelper
{
    private static ?Hashids $hashids = null;

    /**
     * Get Hashids instance
     */
    private static function getHashids(): Hashids
    {
        if (self::$hashids === null) {
            // Use APP_KEY as salt for security
            $salt = config('app.key') . '_tabungan_barcode';
            self::$hashids = new Hashids($salt, 10); // 10 = minimum length
        }

        return self::$hashids;
    }

    /**
     * Encode ID to hash
     */
    public static function encode(int $id): string
    {
        return self::getHashids()->encode($id);
    }

    /**
     * Decode hash to ID
     */
    public static function decode(string $hash): ?int
    {
        $decoded = self::getHashids()->decode($hash);
        return !empty($decoded) ? $decoded[0] : null;
    }

    /**
     * Encode multiple IDs
     */
    public static function encodeMultiple(array $ids): string
    {
        return self::getHashids()->encode(...$ids);
    }

    /**
     * Decode to multiple IDs
     */
    public static function decodeMultiple(string $hash): array
    {
        return self::getHashids()->decode($hash);
    }
}
