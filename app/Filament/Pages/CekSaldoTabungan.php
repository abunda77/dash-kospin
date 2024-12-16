<?php

namespace App\Filament\Pages;

use App\Models\Tabungan;
use App\Models\SaldoTabungan;
use App\Models\TransaksiTabungan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Log;

class CekSaldoTabungan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.cek-saldo-tabungan';

    protected static ?string $navigationLabel = 'Cek Saldo Tabungan';
    public static function getNavigationGroup(): ?string
            {
                return 'Tabungan';
            }

    public $no_tabungan = '';
    public $saldo_akhir = 0;
    public $saldo_awal = 0;
    public $tabungan = null;

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('no_tabungan')
                    ->label('Nomor Tabungan')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function cekSaldo()
    {
        try {
            $this->validate([
                'no_tabungan' => 'required',
            ]);

            $this->tabungan = Tabungan::where('no_tabungan', $this->no_tabungan)->first();

            if (!$this->tabungan) {
                Notification::make()
                    ->title('Nomor tabungan tidak ditemukan')
                    ->danger()
                    ->send();
                return;
            }

            // Ambil saldo awal dari tabel tabungan
            $this->saldo_awal = $this->tabungan->saldo;

            // Hitung total dari transaksi
            $totalDebit = TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
                ->where('jenis_transaksi', 'debit')
                ->sum('jumlah');

            $totalKredit = TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
                ->where('jenis_transaksi', 'kredit')
                ->sum('jumlah');

            // Hitung saldo akhir dengan menambahkan saldo awal
            $this->saldo_akhir = $this->saldo_awal + ($totalDebit - $totalKredit);

            // Update atau create saldo tabungan
            SaldoTabungan::updateOrCreate(
                ['id_tabungan' => $this->tabungan->id],
                ['saldo_akhir' => $this->saldo_akhir]
            );

            Log::info('Saldo berhasil dihitung', [
                'no_tabungan' => $this->no_tabungan,
                'saldo_awal' => $this->saldo_awal,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir' => $this->saldo_akhir
            ]);

            Notification::make()
                ->title('Saldo berhasil diperbarui')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Error saat cek saldo: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan saat mengecek saldo')
                ->danger()
                ->send();
        }
    }
}
