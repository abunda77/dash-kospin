<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BlacklistService
{
    /**
     * Cek apakah alamat email termasuk blacklist.
     *
     * Entri tanpa "@" (atau berawalan "@" / "*@") memblokir seluruh domain
     * beserta subdomain. Entri lain dicocokkan sebagai pola glob terhadap
     *
     * alamat email penuh, sehingga "aba*@gmail.com" memblokir
     * "aba123@gmail.com" tanpa memblokir seluruh gmail.com.
     */
    public function isBlacklistedEmail(?string $email): bool
    {
        if (blank($email) || ! str_contains($email, '@')) {
            return false;
        }

        $email = strtolower(trim($email));
        $domain = (string) substr(strrchr($email, '@'), 1);

        if ($domain === '') {
            return false;
        }

        foreach ($this->entries('email') as $blocked) {
            if (str_contains($blocked, '@') && ! str_starts_with($blocked, '*@') && ! str_starts_with($blocked, '@')) {
                if (Str::is($blocked, $email)) {
                    return true;
                }

                continue;
            }

            $blockedDomain = str_starts_with($blocked, '*@')
                ? substr($blocked, 2)
                : ltrim($blocked, '@');

            if ($blockedDomain !== '' && ($domain === $blockedDomain || str_ends_with($domain, '.'.$blockedDomain))) {
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
     * Validasi satu nilai entri bertipe email.
     *
     * Bentuk yang diterima: "example.com", "@example.com", "*@example.com",
     *
     * atau local-part dengan wildcard seperti "aba*@example.com" dan
     * "*ereo@example.com".
     */
    public static function isValidEmailEntry(string $value): bool
    {
        $value = strtolower(trim($value));

        if ($value === '') {
            return false;
        }

        if (! str_contains($value, '@')) {
            return self::isValidDomain($value);
        }

        if (str_starts_with($value, '*@') || str_starts_with($value, '@')) {
            return self::isValidDomain(ltrim($value, '*@'));
        }

        $separator = (int) strrpos($value, '@');
        $localPart = substr($value, 0, $separator);
        $domain = substr($value, $separator + 1);

        if (! preg_match('/^[a-z0-9._%+\-*]+$/', $localPart)) {
            return false;
        }

        return self::isValidDomain($domain);
    }

    private static function isValidDomain(string $domain): bool
    {
        return str_contains($domain, '.')
            && (bool) filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
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
