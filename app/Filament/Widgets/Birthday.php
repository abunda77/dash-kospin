<?php

namespace App\Filament\Widgets;

use App\Models\BirthdayGreeting;
use App\Models\BirthdayLog;
use App\Models\Profile;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Birthday extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

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
                    ->formatStateUsing(fn ($record) => $record->first_name.' '.$record->last_name)
                    ->sortable(),
                TextColumn::make('birthday')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('Whatsapp')
                    ->url(fn ($record) => 'https://wa.me/'.$record->whatsapp)
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Action::make('send_wish')
                    ->label('Kirim Ucapan')
                    ->icon('heroicon-o-gift')
                    ->action(function (Profile $record) {
                        $this->dispatch('spin-start');

                        $greeting = BirthdayGreeting::inRandomOrder()->first();

                        $message = str_replace(
                            ['{{first_name}}', '{{last_name}}', '{{birthday}}', '\n'],
                            [$record->first_name, $record->last_name, $record->birthday->format('d F'), "\n"],
                            $greeting->message
                        );

                        $whatsapp = preg_replace('/^(\+62|62|0)/', '', $record->whatsapp);
                        $whatsapp = '62'.$whatsapp;

                        $response = send_whatsapp_api($whatsapp, $message);

                        BirthdayLog::create([
                            'id_profile' => $record->id_user,
                            'status_sent' => $response->status() === 200 ? 1 : 0,
                            'date_sent' => now(),
                        ]);

                        $this->dispatch('birthday-log-updated');

                        Notification::make()
                            ->title($response->status() === 200 ?
                                'Ucapan telah terkirim' :
                                'Gagal mengirim ucapan')
                            ->status($response->status() === 200 ? 'success' : 'danger')
                            ->send();

                        $this->dispatch('spin-stop');
                    })
                    ->extraAttributes([
                        'x-data' => '{ spinning: false }',
                        'x-on:spin-start' => 'spinning = true',
                        'x-on:spin-stop' => 'spinning = false',
                        'x-bind:class' => "{ 'animate-spin': spinning }",
                    ]),
            ])
            ->heading('Ulang Tahun Hari Ini')
            ->defaultSort('first_name')
            ->paginated(false);
    }
}
