<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\RawJs;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Button;
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

                TextInput::make('jangkaWaktu')
                    ->label('Jangka Waktu (bulan)')
                    ->numeric()
                    ->required(),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('hitung')
                ->label('Hitung')
                ->action('calculateAngsuran')
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

        $bungaPerBulan = ($pokok * ($bungaPerTahun / 100)) / $jangkaWaktu;
        $angsuranPokok = $pokok / $jangkaWaktu;
        $totalAngsuran = $angsuranPokok + $bungaPerBulan;

        $this->angsuranList = [];
        $sisaPokok = $pokok;

        for ($i = 1; $i <= $jangkaWaktu; $i++) {
            $this->angsuranList[] = [
                'bulan_ke' => $i,
                'pokok' => $angsuranPokok,
                'bunga' => $bungaPerBulan,
                'angsuran' => $totalAngsuran,
            ];

            $sisaPokok -= $angsuranPokok;
        }

        Notification::make()
            ->title('Simulasi berhasil dihitung')
            ->success()
            ->send();
    }
}
