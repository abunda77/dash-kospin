<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerMobileResource\Pages;
use App\Filament\Resources\BannerMobileResource\RelationManagers;
use App\Models\BannerMobile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerMobileResource extends Resource
{
    protected static ?string $model = BannerMobile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Banner Mobile';
    protected static ?string $title = 'Banner Mobile';
    protected static ?string $navigationGroup = 'API';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255)
                    ->hidden()
                    ->dehydrated(true),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'square' => 'Square',
                        'rectangle' => 'Rectangle'
                    ]),
                Forms\Components\Textarea::make('note')
                    ->maxLength(65535),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->required()
                    ->directory('public/banner-mobile')
                    ->visibility('public')
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $url = asset('storage/banner-mobile/' . $state);
                            $set('url', $url);
                        }
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Genereate table columns
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('url'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('note'),
                Tables\Columns\ImageColumn::make('image')
                    ->size(100)
                    ->circular()
                    ->visibility('public'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListBannerMobiles::route('/'),
            'create' => Pages\CreateBannerMobile::route('/create'),
            'edit' => Pages\EditBannerMobile::route('/{record}/edit'),
        ];
    }
}
