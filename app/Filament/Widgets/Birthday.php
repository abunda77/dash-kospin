<?php

namespace App\Filament\Widgets;

use App\Models\Profile;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\BirthdayLog;
use Filament\Notifications\Notification;

class Birthday extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
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
                    ->label('Nama Lengkap')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->sortable(),
                TextColumn::make('birthday')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('Whatsapp')
                    ->url(fn ($record) => "https://wa.me/" . $record->whatsapp)
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Action::make('send_wish')
                    ->label('Kirim Ucapan')
                    ->icon('heroicon-o-gift')
                    ->action(function (Profile $record) {
                        $this->dispatch('spin-start');

                        BirthdayLog::create([
                            'id_profile' => $record->id_user,
                            'status_sent' => 1,
                            'date_sent' => now()
                        ]);

                        Notification::make()
                            ->title('Ucapan telah terkirim')
                            ->success()
                            ->send();

                        $this->dispatch('spin-stop');
                    })
                    ->extraAttributes([
                        'x-data' => '{ spinning: false }',
                        'x-on:spin-start' => 'spinning = true',
                        'x-on:spin-stop' => 'spinning = false',
                        'x-bind:class' => "{ 'animate-spin': spinning }"
                    ])
            ])
            ->heading('Ulang Tahun Hari Ini')
            ->defaultSort('first_name')
            ->paginated(false);
    }
}
