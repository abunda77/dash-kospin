<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\RawJs;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Button;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class SimulasiKredit extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static string $view = 'filament.pages.simulasi-kredit';
    protected static ?string $navigationGroup = 'Pinjaman';
    protected static ?string $navigationLabel = 'Simulasi Kredit';
    protected static ?string $title = 'Simulasi Kredit';

    public $nominalPinjaman;
    public $bunga;
    public $jangkaWaktu;
    public $jenisJangkaWaktu = 'bulan';
    public $angsuranList = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nominalPinjaman')
                    ->label('Nominal Pinjaman')
                    ->prefix('Rp.')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->required(),

                TextInput::make('bunga')
                    ->label('Bunga (%)')
                    ->numeric()
                    ->required(),

                Grid::make(2)
                    ->schema([
                        TextInput::make('jangkaWaktu')
                            ->label('Jangka Waktu')
                            ->numeric()
                            ->required(),

                        Select::make('jenisJangkaWaktu')
                            ->label('Satuan Waktu')
                            ->options([
                                'bulan' => 'Bulan',
                                'minggu' => 'Minggu',
                            ])
                            ->default('bulan')
                            ->required(),
                    ]),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('hitung')
                ->label('Hitung')
                ->action('calculateAngsuran'),

            Action::make('cetakPDF')
                ->label('Cetak PDF')
                ->action('generatePDF')
                ->visible(fn () => count($this->angsuranList) > 0)
                ->icon('heroicon-o-printer'),

            Action::make('cetakFormPengajuan')
                ->label('Cetak Form Pengajuan')
                ->action('generateFormPengajuan')
                ->icon('heroicon-o-document')
                ->color('danger'),
        ];
    }

    protected function formatCurrency($number): string
    {
        return number_format((float) $number, 0, ',', '.');
    }

    public function calculateAngsuran(): void
    {
        $pokok = (float) str_replace([',', '.'], '', $this->nominalPinjaman);
        $bungaPerTahun = (float) $this->bunga;
        $jangkaWaktu = (int) $this->jangkaWaktu;
        $jenisJangkaWaktu = $this->jenisJangkaWaktu;

        // Perhitungan bunga berdasarkan jenis jangka waktu
        if ($jenisJangkaWaktu === 'bulan') {
            // Perhitungan untuk bulanan (sama seperti sebelumnya)
            $bungaPerPeriode = ($pokok * ($bungaPerTahun / 100)) / $jangkaWaktu;
            $angsuranPokok = $pokok / $jangkaWaktu;
            $totalAngsuran = $angsuranPokok + $bungaPerPeriode;
        } else {
            // Perhitungan untuk mingguan
            // Bunga tetap dihitung per tahun, tapi dibagi per minggu
            $totalBunga = ($pokok * ($bungaPerTahun / 100) * $jangkaWaktu) / 48;
            $bungaPerPeriode = $totalBunga / $jangkaWaktu;
            $angsuranPokok = $pokok / $jangkaWaktu;
            $totalAngsuran = $angsuranPokok + $bungaPerPeriode;
        }

        $this->angsuranList = [];
        $sisaPokok = $pokok;

        for ($i = 1; $i <= $jangkaWaktu; $i++) {
            $this->angsuranList[] = [
                'bulan_ke' => $i,
                'pokok' => $angsuranPokok,
                'bunga' => $bungaPerPeriode,
                'angsuran' => $totalAngsuran,
            ];

            $sisaPokok -= $angsuranPokok;
        }

        Notification::make()
            ->title('Simulasi berhasil dihitung')
            ->success()
            ->send();
    }

    public function generatePDF()
    {
        $data = [
            'angsuranList' => $this->angsuranList,
            'nominalPinjaman' => $this->nominalPinjaman,
            'bunga' => $this->bunga,
            'jangkaWaktu' => $this->jangkaWaktu,
            'jenisJangkaWaktu' => $this->jenisJangkaWaktu,
            'totalBunga' => collect($this->angsuranList)->sum('bunga'),
            'totalAngsuran' => collect($this->angsuranList)->sum('angsuran'),
        ];

        $pdf = Pdf::loadView('pdf.simulasi-kredit', $data);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'simulasi-kredit.pdf'
        );
    }

    public function generateFormPengajuan()
    {
        $data = [
            'nominalPinjaman' => (float) str_replace(['Rp', '.', ' '], '', $this->nominalPinjaman),
            'bunga' => $this->bunga,
            'jangkaWaktu' => $this->jangkaWaktu,
            'jenisJangkaWaktu' => $this->jenisJangkaWaktu,
        ];

        $pdf = Pdf::loadView('pdf.form-pengajuan-kredit', $data);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'form-pengajuan-kredit.pdf'
        );
    }
}
