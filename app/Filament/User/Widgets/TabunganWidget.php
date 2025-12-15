<?php

namespace App\Filament\User\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Facades\Auth;

class TabunganWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '💰 Tabungan Saya';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('no_tabungan')
                    ->label('No. Tabungan')
                    ->weight('bold')
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('produkTabungan.nama_produk')
                    ->label('Produk')
                    ->badge()
                    ->color('info'),

                TextColumn::make('saldo_akhir')
                    ->label('Saldo')
                    ->state(function (Tabungan $record) {
                        $saldoAwal = $record->saldo;
                        $totalDebit = TransaksiTabungan::where('id_tabungan', $record->id)
                            ->where('jenis_transaksi', 'debit')
                            ->sum('jumlah');
                        $totalKredit = TransaksiTabungan::where('id_tabungan', $record->id)
                            ->where('jenis_transaksi', 'kredit')
                            ->sum('jumlah');
                        return $saldoAwal + ($totalDebit - $totalKredit);
                    })
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('status_rekening')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif', 'Aktif' => 'success',
                        'tutup', 'Tutup' => 'danger',
                        'blokir', 'Blokir' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada tabungan')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return Tabungan::query()->whereRaw('1 = 0');
        }

        return Tabungan::query()
            ->where('id_profile', $profile->id_user)
            ->where('status_rekening', 'aktif')
            ->with('produkTabungan')
            ->limit(5);
    }
}
