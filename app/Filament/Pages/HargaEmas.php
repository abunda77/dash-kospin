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
            $response = Http::get('https://www.indogold.id/harga-emas-hari-ini');
            $html = $response->body();

            $crawler = new Crawler($html);

            // Definisikan kepingan yang ingin diambil
            $targetKepingan = [
                '0.5 gram', '1.0 gram', '2.0 gram', '3.0 gram', '5.0 gram',
                '10.0 gram', '25.0 gram', '50.0 gram', '100.0 gram'
            ];

            $collectedData = [];

            $crawler->filter('table tr')->each(function ($tr) use ($targetKepingan, &$collectedData) {
                $columns = $tr->filter('td')->each(function ($td) {
                    return trim($td->text());
                });

                if (count($columns) >= 3 && in_array($columns[0], $targetKepingan)) {
                    // Hanya ambil data pertama untuk setiap kepingan
                    if (!isset($collectedData[$columns[0]])) {
                        $collectedData[$columns[0]] = [
                            'kepingan' => $columns[0],
                            'antam' => $columns[1],
                            'ubs' => $columns[2],
                        ];
                    }
                }
            });

            // Urutkan data sesuai urutan targetKepingan
            $this->hargaEmas = array_values(array_filter(
                array_map(function($kepingan) use ($collectedData) {
                    return $collectedData[$kepingan] ?? null;
                }, $targetKepingan)
            ));

        } catch (\Exception $e) {
            $this->hargaEmas = [];
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
            $dompdf->setPaper('A4', 'portrait');

            $data = [
                'hargaEmas' => $this->hargaEmas,
                'keterangan' => [
                    'setoran_awal' => '5%',
                    'administrasi' => '0.5%',
                    'bunga_tahunan' => '5%',
                    'tenor' => ['12', '24', '36', '48', '60'],
                    'info_tambahan' => [
                        'Jika terjadi gagal bayar lebih dari 3 bulan berturut-turut, maka dana yang sudah disetor akan dihitung dan dikembalikan kepada nasabah secara proposional setelah emas dijual sesuai harga yang berlaku pada hari tersebut.',
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
