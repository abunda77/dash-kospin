<?php

namespace App\Filament\User\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PinjamanWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?string $heading = '💳 Pinjaman Aktif';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('no_pinjaman')
                    ->label('No. Pinjaman')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('produkPinjaman.nama_produk')
                    ->label('Produk')
                    ->badge()
                    ->color('info'),

                TextColumn::make('sisa_pinjaman')
                    ->label('Sisa')
                    ->state(function (Pinjaman $record) {
                        $lastTransaksi = $record->transaksiPinjaman->sortByDesc('id')->first();
                        return $lastTransaksi ? $lastTransaksi->sisa_pinjaman : $record->jumlah_pinjaman;
                    })
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->color(fn ($state) => Carbon::parse($state)->isPast() ? 'danger' : 'success'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada pinjaman aktif')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return Pinjaman::query()->whereRaw('1 = 0');
        }

        return Pinjaman::query()
            ->where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->with(['produkPinjaman', 'transaksiPinjaman'])
            ->limit(5);
    }
}
