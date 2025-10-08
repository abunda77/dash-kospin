<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarcodeScanLogResource\Pages;
use App\Filament\Resources\BarcodeScanLogResource\RelationManagers;
use App\Models\BarcodeScanLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarcodeScanLogResource extends Resource
{
    protected static ?string $model = BarcodeScanLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scan Logs';

    protected static ?string $modelLabel = 'Barcode Scan Log';

    protected static ?string $pluralModelLabel = 'Barcode Scan Logs';
   
    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Scan Information')
                    ->schema([
                        Forms\Components\Select::make('tabungan_id')
                            ->relationship('tabungan', 'no_tabungan')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('hash')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\DateTimePicker::make('scanned_at')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->maxLength(45),
                        Forms\Components\Textarea::make('user_agent')
                            ->rows(2),
                        Forms\Components\TextInput::make('referer')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_mobile')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->maxLength(2),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tabungan.no_tabungan')
                    ->label('No. Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tabungan.profile.first_name')
                    ->label('Nasabah')
                    ->formatStateUsing(fn ($record) => 
                        $record->tabungan->profile->first_name . ' ' . 
                        $record->tabungan->profile->last_name
                    )
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-globe-alt'),
                Tables\Columns\IconColumn::make('is_mobile')
                    ->label('Mobile')
                    ->boolean()
                    ->trueIcon('heroicon-o-device-phone-mobile')
                    ->falseIcon('heroicon-o-computer-desktop'),
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Scanned At')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->scanned_at->format('d/m/Y H:i:s')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scanned_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tabungan_id')
                    ->relationship('tabungan', 'no_tabungan')
                    ->searchable()
                    ->preload()
                    ->label('Rekening'),
                Tables\Filters\Filter::make('is_mobile')
                    ->query(fn (Builder $query): Builder => $query->where('is_mobile', true))
                    ->label('Mobile Only')
                    ->toggle(),
                Tables\Filters\Filter::make('scanned_at')
                    ->form([
                        Forms\Components\DatePicker::make('scanned_from')
                            ->label('Scanned From'),
                        Forms\Components\DatePicker::make('scanned_until')
                            ->label('Scanned Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scanned_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scanned_at', '>=', $date),
                            )
                            ->when(
                                $data['scanned_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scanned_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBarcodeScanLogs::route('/'),
            'view' => Pages\ViewBarcodeScanLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Logs are created automatically
    }
}
