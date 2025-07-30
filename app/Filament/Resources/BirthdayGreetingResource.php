<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BirthdayGreetingResource\Pages;
use App\Filament\Resources\BirthdayGreetingResource\RelationManagers;
use App\Models\BirthdayGreeting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Textarea;

class BirthdayGreetingResource extends Resource
{
    protected static ?string $model = BirthdayGreeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Promotion';
    protected static ?string $navigationLabel = 'Ucapan Ulang Tahun';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required()
                    // ->toolbarButtons([
                    //     'bold',
                    //     'italic',
                    //     'strike',
                    //     'orderedList',
                    //     'bulletList',
                    //     'blockquote',
                    //     'codeBlock',
                    //     'undo',
                    //     'redo',
                    // ])
                    ->hintAction(
                        Action::make('variables')
                            ->label('Available variables')
                            ->icon('heroicon-m-information-circle')
                            ->action(function () {
                                Notification::make()
                                    ->title('Available variables:')
                                    ->body('{{first_name}}, {{last_name}}, {{birthday}}')
                                    ->info()
                                    ->send();
                            })
                    )
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->html()
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListBirthdayGreetings::route('/'),
            'create' => Pages\CreateBirthdayGreeting::route('/create'),
            'edit' => Pages\EditBirthdayGreeting::route('/{record}/edit'),
        ];
    }
}
