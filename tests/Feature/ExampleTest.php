<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExampleTest extends TestCase
{
    /**
     * Test performa aplikasi dengan stress test
     */
    public function test_aplikasi_stress_test(): void
    {
        $jumlahRequest = 1000; // Jumlah request yang akan dikirim
        $waktuMulai = microtime(true);
        $berhasil = 0;
        $gagal = 0;

        // Kirim request secara bersamaan
        for ($i = 0; $i < $jumlahRequest; $i++) {
            try {
                $response = $this->get('/');
                if ($response->status() === 200) {
                    $berhasil++;
                } else {
                    $gagal++;
                }
            } catch (\Exception $e) {
                $gagal++;
                Log::error("Request gagal: " . $e->getMessage());
            }
        }

        $waktuSelesai = microtime(true);
        $totalWaktu = $waktuSelesai - $waktuMulai;
        $requestPerDetik = $jumlahRequest / $totalWaktu;
        $rataRataResponseTime = ($totalWaktu / $jumlahRequest) * 1000; // dalam millisecond

        // Assertions
        $this->assertGreaterThan(0, $requestPerDetik, 'Request per detik harus lebih dari 0');
        $this->assertLessThan(1000, $rataRataResponseTime, 'Rata-rata response time harus kurang dari 1 detik');
        $this->assertGreaterThan($jumlahRequest * 0.9, $berhasil, 'Minimal 90% request harus berhasil');

        // Log hasil test
        Log::info("Hasil Stress Test:", [
            'total_request' => $jumlahRequest,
            'request_berhasil' => $berhasil,
            'request_gagal' => $gagal,
            'requests_per_second' => round($requestPerDetik, 2),
            'rata_rata_response_time_ms' => round($rataRataResponseTime, 2),
            'total_waktu_detik' => round($totalWaktu, 2)
        ]);
    }

    /**
     * Test response time untuk single request
     */
    public function test_response_time_single_request(): void
    {
        $waktuMulai = microtime(true);

        $response = $this->get('/');

        $waktuSelesai = microtime(true);
        $responseTime = ($waktuSelesai - $waktuMulai) * 1000; // dalam millisecond

        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, 'Response time untuk single request harus kurang dari 500ms');

        Log::info("Single Request Response Time: {$responseTime}ms");
    }
}
