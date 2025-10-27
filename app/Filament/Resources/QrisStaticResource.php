<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrisStaticResource\Pages;
use App\Helpers\QrisHelper;
use App\Models\QrisStatic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QrisStaticResource extends Resource
{
    protected static ?string $model = QrisStatic::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Static QRIS';

    protected static ?string $navigationGroup = 'Payment';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Main Account QRIS'),

                Forms\Components\Section::make('QRIS Input')
                    ->description('Upload QRIS image or paste QRIS string manually')
                    ->schema([
                        Forms\Components\FileUpload::make('qris_image')
                            ->label('Upload QRIS Image')
                            ->image()
                            ->disk('public')
                            ->directory('qris-images')
                            ->imagePreviewHeight('200')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                            ->maxSize(2048)
                            ->helperText('Upload QR code image (PNG/JPG, max 2MB). QRIS string will be extracted automatically.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                if ($state) {
                                    try {
                                        // Get the temporary uploaded file
                                        $file = is_string($state) ? $livewire->getTemporaryUploadedFile($state) : $state;

                                        if ($file) {
                                            $tempPath = $file->getRealPath();
                                            \Log::info('Processing uploaded file from: '.$tempPath);

                                            // Read QRIS from uploaded image
                                            $qrisString = QrisHelper::readQrisFromImage($tempPath);

                                            if ($qrisString) {
                                                $set('qris_string', $qrisString);

                                                // Auto-detect merchant name
                                                $merchantName = QrisHelper::parseMerchantName($qrisString);
                                                $set('merchant_name', $merchantName);

                                                Notification::make()
                                                    ->title('QRIS Detected Successfully')
                                                    ->body("Merchant: {$merchantName}")
                                                    ->success()
                                                    ->send();
                                            } else {
                                                Notification::make()
                                                    ->title('Failed to Read QRIS')
                                                    ->body('Could not extract QRIS string from image. Please paste manually.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        \Log::error('Error processing QRIS upload: '.$e->getMessage());
                                        Notification::make()
                                            ->title('Upload Error')
                                            ->body('Error processing image. Please try again.')
                                            ->danger()
                                            ->send();
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('qris_string')
                            ->label('Static QRIS String')
                            ->required()
                            ->rows(4)
                            ->placeholder('Paste your static QRIS code here or upload image above...')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && ! $get('merchant_name')) {
                                    // Auto-detect merchant name when pasting
                                    $merchantName = QrisHelper::parseMerchantName($state);
                                    $set('merchant_name', $merchantName);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('merchant_name')
                    ->label('Merchant Name')
                    ->maxLength(255)
                    ->placeholder('Auto-detected from QRIS')
                    ->helperText('Will be auto-filled when QRIS is uploaded or pasted'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('qris_image')
                    ->label('QR Image')
                    ->disk('public')
                    ->size(60)
                    ->defaultImageUrl(url('/images/qr-placeholder.png'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (QrisStatic $record): string => $record->description ?? ''),

                Tables\Columns\TextColumn::make('merchant_name')
                    ->label('Merchant')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (QrisStatic $record) => view('filament.resources.qris-static.view-modal', ['record' => $record]))
                    ->modalWidth('2xl'),
                Tables\Actions\Action::make('generate')
                    ->label('Generate Dynamic')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->url(fn () => \App\Filament\Pages\QrisDynamicGenerator::getUrl())
                    ->openUrlInNewTab(false),
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
            'index' => Pages\ListQrisStatics::route('/'),
            'create' => Pages\CreateQrisStatic::route('/create'),
            'edit' => Pages\EditQrisStatic::route('/{record}/edit'),
        ];
    }
}
