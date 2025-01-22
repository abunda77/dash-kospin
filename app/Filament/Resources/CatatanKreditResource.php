<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatatanKreditResource\Pages;
use App\Filament\Resources\CatatanKreditResource\RelationManagers;
use App\Models\CatatanKredit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Contracts\View\View;

class CatatanKreditResource extends Resource
{
    protected static ?string $model = CatatanKredit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pinjaman';
    protected static ?string $navigationLabel = 'Catatan Kredit';
    protected static ?string $title = 'Catatan Kredit';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('write_by')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_nasabah')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status_notes')
                    ->options([
                        'open' => 'Open',
                        'close' => 'Close'
                    ])
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('write_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('status_notes')
                    ->options([
                        'open' => 'Open',
                        'close' => 'Close'
                    ])
                    ->afterStateUpdated(function($record, $state) {
                        $record->status_notes = $state;
                        $record->save();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCatatanKredits::route('/'),
            'create' => Pages\CreateCatatanKredit::route('/create'),
            'edit' => Pages\EditCatatanKredit::route('/{record}/edit'),
            'view' => Pages\ViewCatatanKredit::route('/{record}'),
        ];
    }
}
