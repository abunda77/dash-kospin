<?php

if (! function_exists('terbilang')) {
    /**
     * Mengkonversi angka menjadi kata-kata dalam Bahasa Indonesia.
     *
     * @param  numeric  $angka
     */
    function terbilang($angka): string
    {
        if ($angka < 0) {
            return 'minus '.terbilang(abs($angka));
        }

        $kata = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $kata[$angka];
        } elseif ($angka < 20) {
            return $kata[$angka - 10].' belas';
        } elseif ($angka < 100) {
            return terbilang($angka / 10).' puluh '.terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'seratus '.terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return terbilang($angka / 100).' ratus '.terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'seribu '.terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return terbilang($angka / 1000).' ribu '.terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return terbilang($angka / 1000000).' juta '.terbilang($angka % 1000000);
        }

        return terbilang($angka / 1000000000).' milyar '.terbilang($angka % 1000000000);
    }
}

if (! function_exists('format_rupiah')) {
    /**
     * Format angka ke dalam format mata uang Rupiah.
     *
     * @param  numeric  $angka
     */
    function format_rupiah($angka): string
    {
        return 'Rp '.number_format($angka, 0, ',', '.');
    }
}

if (! function_exists('format_hari_lalu')) {
    /**
     * Format jumlah hari menjadi format yang mudah dibaca.
     * Contoh: 7 hari, 1 bulan 3 hari, 2 tahun 5 bulan
     */
    function format_hari_lalu(int $hari): string
    {
        if ($hari < 0) {
            return '0 hari';
        }

        if ($hari < 30) {
            return $hari.' hari';
        }

        if ($hari < 365) {
            $bulan = floor($hari / 30);
            $sisaHari = $hari % 30;

            $result = $bulan.' bulan';
            if ($sisaHari > 0) {
                $result .= ' '.$sisaHari.' hari';
            }

            return $result;
        }

        $tahun = floor($hari / 365);
        $sisaBulan = floor(($hari % 365) / 30);
        $sisaHari = ($hari % 365) % 30;

        $result = $tahun.' tahun';
        if ($sisaBulan > 0) {
            $result .= ' '.$sisaBulan.' bulan';
        }
        if ($sisaHari > 0) {
            $result .= ' '.$sisaHari.' hari';
        }

        return $result;
    }
}

if (! function_exists('send_whatsapp_api')) {
    function send_whatsapp_api(string $phone, string $message): \Illuminate\Http\Client\Response
    {
        $url = sprintf('http://%s:%s/send/message', env('WHATSAPP_IP'), env('WHATSAPP_PORT'));

        return \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode(env('WHATSAPP_AUTH')),
            'X-Device-Id' => env('WHATSAPP_DEVICE_ID'),
            'Content-Type' => 'application/json',
        ])->post($url, [
            'phone' => $phone.'@s.whatsapp.net',
            'message' => $message,
            'reply_message_id' => '',
            'is_forwarded' => false,
            'action' => env('WHATSAPP_ACTION', 'stop'),
            'duration' => (int) env('WHATSAPP_DURATION', 86400),
        ]);
    }
}
