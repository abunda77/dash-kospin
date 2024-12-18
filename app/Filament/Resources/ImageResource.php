<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageResource\Pages;
use App\Models\Image;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Gambar';
    protected static ?string $title = 'Gambar';

    public static function getNavigationGroup(): ?string
            {
                return 'Settings';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->image()
                    // ->imageResizeMode('cover')
                    // ->imageCropAspectRatio('16:9')
                    // ->imageResizeTargetWidth('1920')
                    // ->imageResizeTargetHeight('1080')
                    // ->imageEditor()
                    ->directory('public/images')
                    ->visibility('public'),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->size(100)
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }
}
