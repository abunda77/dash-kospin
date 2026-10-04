<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class BlacklistService
{
    /**
     * Cek apakah domain email termasuk blacklist.
     */
    public function isBlacklistedEmailDomain(?string $email): bool
    {
        if (blank($email) || ! str_contains($email, '@')) {
            return false;
        }

        $domain = strtolower((string) substr(strrchr($email, '@'), 1));

        if ($domain === '') {
            return false;
        }

        foreach ($this->entries('email') as $blocked) {
            $blocked = ltrim($blocked, '*@');

            if ($blocked !== '' && ($domain === $blocked || str_ends_with($domain, '.'.$blocked))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah alamat IP termasuk blacklist.
     */
    public function isBlacklistedIp(?string $ip): bool
    {
        if (blank($ip)) {
            return false;
        }

        return in_array(strtolower($ip), $this->entries('ip'), true);
    }

    /**
     * Ambil seluruh entri blacklist untuk tipe tertentu.
     *
     * @return array<int, string>
     */
    public function entries(string $type): array
    {
        return $this->all()[$type] ?? [];
    }

    /**
     * Baca dan cache entri blacklist. Cache di-key dengan signature file
     * agar hasil parsing otomatis diperbarui ketika file berubah.
     *
     * @return array{email: array<int, string>, ip: array<int, string>}
     */
    private function all(): array
    {
        $path = (string) config('security.blacklist_path');
        $signature = md5($path.'|'.(@filemtime($path) ?: 0).'|'.(@filesize($path) ?: 0));

        return Cache::rememberForever(
            'security.blacklist:'.$signature,
            fn (): array => $this->parse($path)
        );
    }

    /**
     * @return array{email: array<int, string>, ip: array<int, string>}
     */
    private function parse(string $path): array
    {
        $entries = ['email' => [], 'ip' => []];

        if (! File::exists($path)) {
            return $entries;
        }

        foreach (File::lines($path) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$type, $value] = array_pad(explode(':', $line, 2), 2, null);

            if ($value === null) {
                continue;
            }

            $type = strtolower(trim((string) $type));
            $value = strtolower(trim($value));

            if ($value !== '' && array_key_exists($type, $entries)) {
                $entries[$type][] = $value;
            }
        }

        return $entries;
    }
}
