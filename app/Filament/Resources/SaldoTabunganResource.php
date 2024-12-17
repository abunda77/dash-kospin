<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaldoTabunganResource\Pages;
use App\Filament\Resources\SaldoTabunganResource\RelationManagers;
use App\Models\SaldoTabungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TransaksiTabungan;
use Filament\Notifications\Notification;

class SaldoTabunganResource extends Resource
{
    protected static ?string $model = SaldoTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Saldo Tabungan';

    protected static ?string $navigationLabel = 'Saldo Tabungan';
    public static function getNavigationGroup(): ?string
            {
                return 'Tabungan';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tabungan.profile.full_name')
                    ->label('Nama Lengkap')
                    ->searchable(false)
                    ->getStateUsing(fn ($record) => "{$record->tabungan->profile->first_name} {$record->tabungan->profile->last_name}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('tabungan.profile.first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->hidden(false),
                Tables\Columns\TextColumn::make('tabungan.profile.last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->hidden(false),
                Tables\Columns\TextColumn::make('tabungan.no_tabungan')
                    ->label('No Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_akhir')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 2, ',', '.'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                BulkAction::make('updateSaldoAkhir')
                    ->label('Update Saldo Akhir')
                    ->icon('heroicon-o-currency-dollar')
                    ->requiresConfirmation()
                    ->modalHeading('Sedang memproses pembaruan saldo...')
                    ->action(function (Collection $records) {
                        foreach ($records as $saldoTabungan) {
                            $tabungan = $saldoTabungan->tabungan;

                            // Ambil saldo awal dari tabel tabungan
                            $saldoAwal = $tabungan->saldo;

                            // Hitung total dari transaksi
                            $totalDebit = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                                ->where('jenis_transaksi', 'debit')
                                ->sum('jumlah');

                            $totalKredit = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                                ->where('jenis_transaksi', 'kredit')
                                ->sum('jumlah');

                            // Hitung saldo akhir
                            $saldoAkhir = $saldoAwal + ($totalDebit - $totalKredit);

                            // Update saldo akhir
                            $saldoTabungan->update([
                                'saldo_akhir' => $saldoAkhir
                            ]);
                        }

                        Notification::make()
                            ->title('Saldo akhir berhasil diperbarui')
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle('Memperbarui saldo...')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSaldoTabungans::route('/'),
        ];
    }
}
