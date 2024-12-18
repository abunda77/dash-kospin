<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Profile;
use App\Models\BirthdayLog;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Birthday extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Ulang Tahun';
    protected static string $view = 'filament.pages.birthday';

    public static function getNavigationGroup(): ?string
    {
        return 'Data Nasabah';
    }

    public function todayTable(Table $table): Table
    {
        $today = Carbon::now()->timezone('Asia/Jakarta');

        return $table
            ->query(
                Profile::query()
                    ->whereMonth('birthday', $today->month)
                    ->whereDay('birthday', $today->day)
            )
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable(),
                TextColumn::make('birthday')
                    ->label('Birthday')
                    ->date()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('Whatsapp')
                    ->url(fn ($record) => "https://wa.me/" . $record->whatsapp)
                    ->openUrlInNewTab(),
                TextColumn::make('birthday_log.status_sent')
                    ->label('Status')
                    ->alignCenter()
                    ->formatStateUsing(function (Profile $record) use ($today) {
                        $log = BirthdayLog::query()
                            ->where('id_profile', $record->id_user)
                            ->whereDate('date_sent', $today->toDateString())
                            ->first();

                        if ($log && $log->status_sent) {
                            return 'Sent';
                        }
                        return 'Not Sent Yet';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sent' => 'success',
                        'Not Sent Yet' => 'danger',
                    }),
            ])
            ->heading('Ulang Tahun Hari Ini')
            ->defaultSort('first_name')
            ->paginated(false);
    }

    public function birthdayLogTable(Table $table): Table
    {
        $today = Carbon::now()->timezone('Asia/Jakarta');

        return $table
            ->query(
                BirthdayLog::query()
                    ->with('profile')
                    ->whereDate('date_sent', $today->toDateString())
            )
            ->columns([
                TextColumn::make('profile.first_name')
                    ->label('First Name')
                    ->sortable(),
                TextColumn::make('profile.last_name')
                    ->label('Last Name')
                    ->sortable(),
                TextColumn::make('profile.birthday')
                    ->label('Birthday')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_sent')
                    ->label('Sent Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status_sent')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string =>
                        $state ? 'success' : 'danger'
                    )
                    ->formatStateUsing(fn (bool $state): string =>
                        $state ? 'Sent' : 'Not Sent'
                    ),
            ])
            ->heading('Log Pengiriman Hari Ini')
            ->defaultSort('date_sent', 'desc')
            ->paginated(false);
    }

    public function tomorrowTable(Table $table): Table
    {
        $tomorrow = Carbon::tomorrow()->timezone('Asia/Jakarta');

        return $table
            ->query(
                Profile::query()
                    ->whereMonth('birthday', $tomorrow->month)
                    ->whereDay('birthday', $tomorrow->day)
            )
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable(),
                TextColumn::make('birthday')
                    ->label('Birthday')
                    ->date()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('Whatsapp')
                    ->url(fn ($record) => "https://wa.me/" . $record->whatsapp)
                    ->openUrlInNewTab(),
            ])
            ->heading('Ulang Tahun Besok')
            ->defaultSort('first_name')
            ->paginated(false);
    }

    public function table(Table $table): Table
    {
        return $this->todayTable($table);
    }

    protected function getTables(): array
    {
        return [
            'todayTable',
            'birthdayLogTable',
            'tomorrowTable'
        ];
    }
}
