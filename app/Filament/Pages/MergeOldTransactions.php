<?php

namespace App\Filament\Pages;

use App\Models\Tabungan;
use Filament\Pages\Page;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class MergeOldTransactions extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Gabung Transaksi Lama';
    protected static ?string $title = 'Gabung Transaksi Lama';
    protected static ?string $slug = 'merge-old-transactions';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.merge-old-transactions';

    public static function getNavigationGroup(): ?string
    {
        return 'Tabungan';
    }

    public $idTabungan;
    public $tabungan;

    public function mount($id_tabungan = null): void
    {
        $this->idTabungan = $id_tabungan;
        $this->loadTabungan();
    }

    protected function loadTabungan(): void
    {
        $this->tabungan = Tabungan::with(['profile', 'produkTabungan'])
            ->find($this->idTabungan);

        if (!$this->tabungan) {
            Notification::make()
                ->title('Tabungan tidak ditemukan')
                ->danger()
                ->persistent()
                ->send();

            $this->redirectToMutasi();
        }
    }

    public function mergeTransactions(): void
    {
        try {
            DB::transaction(function () {
                $cutoffDate = now()->subYear()->startOfYear();
                $oldTransactions = $this->getOldTransactions($cutoffDate);

                if ($oldTransactions->isEmpty()) {
                    throw new Halt($this->getNoTransactionsMessage($cutoffDate));
                }

                $totalBalance = $this->calculateTotalBalance($oldTransactions);
                $this->createOpeningTransaction($totalBalance, $cutoffDate);
                $deletedCount = $this->deleteOldTransactions($cutoffDate);

                Notification::make()
                    ->title('Penggabungan transaksi berhasil')
                    ->body("Berhasil menggabungkan {$deletedCount} transaksi lama.")
                    ->success()
                    ->persistent()
                    ->send();

                $this->redirectToMutasi();
            });
        } catch (Halt $e) {
            Notification::make()
                ->title('Perhatian')
                ->body($e->getMessage())
                ->warning()
                ->persistent()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error in mergeTransactions: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getOldTransactions($cutoffDate)
    {
        return TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
            ->where('tanggal_transaksi', '<', $cutoffDate)
            ->orderBy('tanggal_transaksi', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    protected function calculateTotalBalance($transactions): float
    {
        return $transactions->reduce(function ($total, $transaction) {
            $amount = $transaction->jumlah;
            return $transaction->jenis_transaksi === 'debit'
                ? $total + $amount
                : $total - $amount;
        }, 0);
    }

    protected function createOpeningTransaction($totalBalance, $cutoffDate): void
    {
        $openingTransaction = new TransaksiTabungan();
        $openingTransaction->id_tabungan = $this->tabungan->id;
        $openingTransaction->tanggal_transaksi = $cutoffDate;

        $timestamp = date('YmdHis');
        $openingTransaction->kode_transaksi = "00G{$timestamp}";

        $openingTransaction->keterangan = 'Saldo awal dari penggabungan transaksi sebelum ' . $cutoffDate->format('d-m-Y');
        $openingTransaction->jenis_transaksi = $totalBalance >= 0 ? 'debit' : 'kredit';
        $openingTransaction->jumlah = abs($totalBalance);

        if ($user = Auth::guard('admin')->user()) {
            $openingTransaction->kode_teller = $user->id;
        } else {
            $openingTransaction->kode_teller = 1;
        }

        $openingTransaction->save();
    }

    protected function deleteOldTransactions($cutoffDate): int
    {
        return TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
            ->where('tanggal_transaksi', '<', $cutoffDate)
            ->where('kode_transaksi', 'not like', '00G%')
            ->delete();
    }

    protected function getNoTransactionsMessage($cutoffDate): string
    {
        return 'Tidak ada transaksi sebelum ' . $cutoffDate->format('d-m-Y') . ' yang perlu digabung.';
    }

    protected function redirectToMutasi(): void
    {
        $this->redirect(route('filament.admin.pages.mutasi-tabungan-v2'));
    }
}
