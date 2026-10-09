<?php

namespace App\Filament\Pages;

use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class Tokenize extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Tokenize';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.tokenize';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTokenQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Token')
                    ->searchable(),
                TextColumn::make('tokenable.name')
                    ->label('Nama User')
                    ->searchable(),
                TextColumn::make('tokenable.profile.first_name')
                    ->label('Nama Profil')
                    ->formatStateUsing(fn (?string $state, PersonalAccessToken $record): string => trim($state.' '.($record->tokenable?->profile?->last_name ?? '')))
                    ->placeholder('-'),
                TextColumn::make('last_used_at')
                    ->label('Terakhir Digunakan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('token_count')
                    ->label('Jumlah Token')
                    ->badge()
                    ->color(fn (int $state): string => $state > 1 ? 'warning' : 'gray'),
            ])
            ->filters([
                Filter::make('duplicate')
                    ->label('Hanya token ganda')
                    ->query(fn (Builder $query): Builder => $query->whereIn('tokenable_id', $this->duplicateUserIds())),
            ])
            ->actions([
                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PersonalAccessToken $record): bool => $record->token_count > 1)
                    ->action(function (PersonalAccessToken $record): void {
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('deleteSelected')
                    ->label('Hapus yang Dipilih')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $duplicates = $records->filter(fn (PersonalAccessToken $record): bool => $record->token_count > 1);
                        $skipped = $records->count() - $duplicates->count();

                        $duplicates->each(function (PersonalAccessToken $record): void {
                            $record->delete();
                        });

                        Notification::make()
                            ->success()
                            ->title("{$duplicates->count()} token ganda berhasil dihapus.")
                            ->body($skipped > 0 ? "{$skipped} token tunggal dilewati agar user tidak kehilangan akses." : null)
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('deleteDuplicateTokens')
                    ->label('Hapus Token Ganda')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Menyimpan token yang terakhir digunakan untuk setiap user, lalu menghapus token lainnya.')
                    ->action('deleteDuplicateTokens'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function deleteDuplicateTokens(): void
    {
        $deletedTokens = 0;

        foreach ($this->duplicateUserIds() as $userId) {
            $tokens = PersonalAccessToken::query()
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $userId)
                ->orderByDesc('last_used_at')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $tokens->slice(1)->each(function (PersonalAccessToken $token) use (&$deletedTokens): void {
                $token->delete();
                $deletedTokens++;
            });
        }

        Notification::make()
            ->success()
            ->title("{$deletedTokens} token ganda berhasil dihapus.")
            ->send();
    }

    private function getTokenQuery(): Builder
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->with('tokenable.profile')
            ->select('personal_access_tokens.*')
            ->selectSub(
                DB::table('personal_access_tokens as token_counts')
                    ->selectRaw('count(*)')
                    ->whereColumn('token_counts.tokenable_id', 'personal_access_tokens.tokenable_id')
                    ->where('token_counts.tokenable_type', User::class),
                'token_count',
            );
    }

    private function duplicateUserIds(): array
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->select('tokenable_id')
            ->groupBy('tokenable_id')
            ->havingRaw('count(*) > 1')
            ->pluck('tokenable_id')
            ->all();
    }
}
