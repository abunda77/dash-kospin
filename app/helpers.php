<?php

use Illuminate\Support\Str;

if (!function_exists('terbilang')) {
    /**
     * Mengkonversi angka menjadi kata-kata dalam Bahasa Indonesia.
     *
     * @param  numeric  $angka
     * @return string
     */
    function terbilang($angka): string
    {
        if ($angka < 0) {
            return 'minus ' . terbilang(abs($angka));
        }

        $kata = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $kata[$angka];
        } elseif ($angka < 20) {
            return $kata[$angka - 10] . ' belas';
        } elseif ($angka < 100) {
            return terbilang($angka / 10) . ' puluh ' . terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'seratus ' . terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return terbilang($angka / 100) . ' ratus ' . terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'seribu ' . terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return terbilang($angka / 1000) . ' ribu ' . terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return terbilang($angka / 1000000) . ' juta ' . terbilang($angka % 1000000);
        }

        return terbilang($angka / 1000000000) . ' milyar ' . terbilang($angka % 1000000000);
    }
}

if (!function_exists('format_rupiah')) {
    /**
     * Format angka ke dalam format mata uang Rupiah.
     *
     * @param  numeric  $angka
     * @return string
     */
    function format_rupiah($angka): string
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}
