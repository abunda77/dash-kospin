<?php

namespace App\Filament\Pages;

use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Symfony\Component\DomCrawler\Crawler;

class HargaEmas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Harga Emas';
    protected static ?string $navigationGroup = 'Pinjaman';
    public static function getNavigationGroup(): ?string
    {
        return 'Pinjaman';
    }

    protected static string $view = 'filament.pages.harga-emas';

    public $hargaEmas = [];
    public $hargaEmasTokopedia = null;

    public function mount()
    {
        $this->refreshHargaEmas();
        $this->getHargaEmasTokopedia();
    }

    protected function getActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Data')
                ->action('refreshHargaEmas')
                ->color('danger'),

            Action::make('pdf')
                ->label('Cetak Simulasi Angsuran')
                ->action('generatePDF')
                ->color('success'),
        ];
    }

    public function refreshHargaEmas()
    {
        try {
            // Menggunakan sumber baru galeri24.co.id untuk mendapatkan harga ANTAM
            $response = Http::timeout(30)->get('https://galeri24.co.id/harga-emas#ANTAM');
            $html = $response->body();

            // Data fallback jika gagal scraping
            $fallbackData = [
                '0.5 Gram' => ['ubs' => 'Rp1.051.000', 'antam' => 'Rp1.040.000', 'buyback' => 'Rp898.000'],
                '1.0 Gram' => ['ubs' => 'Rp1.944.000', 'antam' => 'Rp1.976.000', 'buyback' => 'Rp1.796.000'],
                '2.0 Gram' => ['ubs' => 'Rp3.857.000', 'antam' => 'Rp3.889.000', 'buyback' => 'Rp3.592.000'],
                '3.0 Gram' => ['ubs' => 'Rp5.725.000', 'antam' => 'Rp5.808.000', 'buyback' => 'Rp5.388.000'],
                '5.0 Gram' => ['ubs' => 'Rp9.529.000', 'antam' => 'Rp9.646.000', 'buyback' => 'Rp8.980.000'],
                '10.0 Gram' => ['ubs' => 'Rp18.956.000', 'antam' => 'Rp19.234.000', 'buyback' => 'Rp17.961.000'],
                '25.0 Gram' => ['ubs' => 'Rp47.296.000', 'antam' => 'Rp47.954.000', 'buyback' => 'Rp44.683.000'],
                '50.0 Gram' => ['ubs' => 'Rp94.397.000', 'antam' => 'Rp95.827.000', 'buyback' => 'Rp89.366.000'],
                '100.0 Gram' => ['ubs' => 'Rp188.718.000', 'antam' => 'Rp191.573.000', 'buyback' => 'Rp178.732.000'],
            ];

            $crawler = new Crawler($html);
            $dataFound = false;
            $collectedData = [];

            // Definisikan kepingan yang ingin diambil
            $targetKepingan = [
                '0.5 Gram', '1.0 Gram', '2.0 Gram', '3.0 Gram', '5.0 Gram',
                '10.0 Gram', '25.0 Gram', '50.0 Gram', '100.0 Gram'
            ];

            // Definisikan pemetaan berat dari Galeri24 ke format yang kita inginkan
            $weightMapping = [
                '0.5' => '0.5 Gram',
                '1' => '1.0 Gram',
                '2' => '2.0 Gram',
                '3' => '3.0 Gram',
                '5' => '5.0 Gram',
                '10' => '10.0 Gram',
                '25' => '25.0 Gram',
                '50' => '50.0 Gram',
                '100' => '100.0 Gram',
            ];

            // Coba ambil data dari struktur tabel Galeri24
            try {
                // Ambil data ANTAM
                $antamData = [];
                $antamRows = $crawler->filter('.min-w-\[400px\] .grid.grid-cols-5.divide-x:not(:first-child)');

                $antamRows->each(function ($row) use (&$antamData, $weightMapping) {
                    $cells = $row->filter('div');

                    // Pastikan ada 3 kolom (Berat, Harga Jual, Harga Buyback)
                    if ($cells->count() >= 3) {
                        $weight = trim($cells->eq(0)->text());
                        $sellPrice = trim($cells->eq(1)->text());
                        $buybackPrice = trim($cells->eq(2)->text());

                        // Jika berat ada dalam pemetaan, simpan datanya
                        if (isset($weightMapping[$weight])) {
                            $formattedWeight = $weightMapping[$weight];
                            $antamData[$formattedWeight] = [
                                'antam' => $sellPrice,
                                'buyback' => $buybackPrice
                            ];
                        }
                    }
                });

                // Ambil data UBS jika tersedia (untuk contoh kita gunakan fallback)
                $ubsData = [];
                foreach ($targetKepingan as $weight) {
                    if (isset($fallbackData[$weight])) {
                        $ubsData[$weight] = [
                            'ubs' => $fallbackData[$weight]['ubs']
                        ];
                    }
                }

                // Gabungkan data ANTAM dan UBS
                foreach ($targetKepingan as $weight) {
                    if (isset($antamData[$weight]) || isset($ubsData[$weight])) {
                        $collectedData[$weight] = [
                            'kepingan' => $weight,
                            'antam' => $antamData[$weight]['antam'] ?? $fallbackData[$weight]['antam'],
                            'ubs' => $ubsData[$weight]['ubs'] ?? $fallbackData[$weight]['ubs']
                        ];
                    }
                }

                if (count($collectedData) > 0) {
                    $dataFound = true;
                }
            } catch (\Exception $e) {
                Log::error('Error parsing Galeri24 data: ' . $e->getMessage());
            }

            // Jika tidak bisa mengambil data, gunakan data fallback
            if (!$dataFound) {
                foreach ($targetKepingan as $kepingan) {
                    if (isset($fallbackData[$kepingan])) {
                        $collectedData[$kepingan] = [
                            'kepingan' => $kepingan,
                            'ubs' => $fallbackData[$kepingan]['ubs'],
                            'antam' => $fallbackData[$kepingan]['antam']
                        ];
                    }
                }
            }

            // Urutkan data sesuai urutan targetKepingan
            $this->hargaEmas = array_values(array_filter(
                array_map(function($kepingan) use ($collectedData) {
                    return $collectedData[$kepingan] ?? null;
                }, $targetKepingan)
            ));

        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Error saat mengambil data harga emas: ' . $e->getMessage());

            // Fallback ke data statis
            $this->hargaEmas = [
                ['kepingan' => '0.5 Gram', 'ubs' => 'Rp1.051.000', 'antam' => 'Rp1.040.000'],
                ['kepingan' => '1.0 Gram', 'ubs' => 'Rp1.944.000', 'antam' => 'Rp1.976.000'],
                ['kepingan' => '2.0 Gram', 'ubs' => 'Rp3.857.000', 'antam' => 'Rp3.889.000'],
                ['kepingan' => '3.0 Gram', 'ubs' => 'Rp5.725.000', 'antam' => 'Rp5.808.000'],
                ['kepingan' => '5.0 Gram', 'ubs' => 'Rp9.529.000', 'antam' => 'Rp9.646.000'],
                ['kepingan' => '10.0 Gram', 'ubs' => 'Rp18.956.000', 'antam' => 'Rp19.234.000'],
                ['kepingan' => '25.0 Gram', 'ubs' => 'Rp47.296.000', 'antam' => 'Rp47.954.000'],
                ['kepingan' => '50.0 Gram', 'ubs' => 'Rp94.397.000', 'antam' => 'Rp95.827.000'],
                ['kepingan' => '100.0 Gram', 'ubs' => 'Rp188.718.000', 'antam' => 'Rp191.573.000'],
            ];
        }
    }

    public function getHargaEmasTokopedia()
    {
        try {
            $response = Http::get('https://api.tokopedia.com/fintech/api/egold/v2/gold/price', [
                'partner_id' => 5
            ]);

            if ($response->successful()) {
                $this->hargaEmasTokopedia = $response->json()['data'];
            }
        } catch (\Exception $e) {
            $this->hargaEmasTokopedia = null;
        }
    }

    public function generatePDF()
    {
        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'Arial');
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', public_path());

            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'landscape');

            $data = [
                'hargaEmas' => $this->hargaEmas,
                'keterangan' => [
                    'setoran_awal' => '5%',
                    'administrasi' => '0.5%',
                    'bunga_tahunan' => '5%',
                    'tenor' => ['12', '24', '36', '48', '60'],
                    'info_tambahan' => [
                        'Jika terjadi gagal bayar lebih dari 3 bulan maka dana yang sudah disetor akan dihitung dan dikembalikan kepada nasabah secara proposional setelah emas dijual sesuai harga yang berlaku pada hari tersebut.',
                        'Pelunasan dipercepat akan dikenakan penalty 2x margin'
                    ]
                ]
            ];

            $html = view('pdf.harga-emas', $data)->render();
            $dompdf->loadHtml($html);
            $dompdf->render();

            return response()->streamDownload(
                fn () => print($dompdf->output()),
                'harga-emas-' . date('Y-m-d') . '.pdf',
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan saat mencetak PDF')
                ->danger()
                ->send();
            return null;
        }
    }
}
