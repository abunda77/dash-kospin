<?php

namespace App\Filament\Pages;

use App\Models\Pinjaman;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class TableAngsuran extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.table-angsuran';

    public ?string $noPinjaman = '';
    public $pinjaman = null;
    public $angsuranList = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('noPinjaman')
                    ->label('No Pinjaman')
                    ->required()
            ]);
    }

    public function search(): void
    {
        $this->pinjaman = Pinjaman::with(['profile', 'produkPinjaman', 'biayaBungaPinjaman'])
            ->where('no_pinjaman', $this->noPinjaman)
            ->first();

        if ($this->pinjaman) {
            $this->calculateAngsuran();
            Notification::make()
                ->title('Data ditemukan')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Data tidak ditemukan')
                ->danger()
                ->send();
        }
    }

    public function clearSearch(): void
    {
        $this->noPinjaman = '';
        $this->pinjaman = null;
        $this->angsuranList = [];
        $this->form->fill();
    }

    private function calculateAngsuran(): void
    {
        $pokok = $this->pinjaman->jumlah_pinjaman;
        $bungaPerTahun = $this->pinjaman->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $this->pinjaman->jangka_waktu;

        // Hitung bunga per bulan (total bunga setahun dibagi jangka waktu)
        $bungaPerBulan = ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;

        // Hitung angsuran pokok per bulan
        $angsuranPokok = $pokok / $jangkaWaktu;

        // Total angsuran per bulan (tetap)
        $totalAngsuran = $angsuranPokok + $bungaPerBulan;

        $this->angsuranList = [];
        $sisaPokok = $pokok;

        // Ambil tanggal awal pinjaman
        $tanggalJatuhTempo = $this->pinjaman->tanggal_pinjaman->copy();

        for ($i = 1; $i <= $jangkaWaktu; $i++) {
            // Tambah 1 bulan untuk tanggal jatuh tempo berikutnya
            $tanggalJatuhTempo = $tanggalJatuhTempo->addMonth();

            $this->angsuranList[] = [
                'periode' => $i,
                'pokok' => $angsuranPokok,
                'bunga' => $bungaPerBulan,
                'angsuran' => $totalAngsuran,
                'sisa_pokok' => $sisaPokok - $angsuranPokok,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('d/m/Y')
            ];

            $sisaPokok -= $angsuranPokok;
        }
    }
}
