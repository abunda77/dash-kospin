<?php

namespace App\Filament\User\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Deposito;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DepositoWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?string $heading = '🏦 Deposito Aktif';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('nomor_rekening')
                    ->label('No. Rekening')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('nominal_penempatan')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),

                TextColumn::make('rate_bunga')
                    ->label('Bunga')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->color(fn ($state) => Carbon::parse($state)->isPast() ? 'danger' : 'success'),

                IconColumn::make('perpanjangan_otomatis')
                    ->label('ARO')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada deposito aktif')
            ->emptyStateIcon('heroicon-o-building-library');
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return Deposito::query()->whereRaw('1 = 0');
        }

        return Deposito::query()
            ->where('id_user', $profile->id_user)
            ->whereIn('status', ['aktif', 'Aktif', 'active', 'Active'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->limit(5);
    }
}
