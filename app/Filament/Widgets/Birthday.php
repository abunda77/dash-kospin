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
use Illuminate\Support\Facades\Http;
use App\Models\BirthdayGreeting;

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

                        $greeting = BirthdayGreeting::inRandomOrder()->first();

                        $message = str_replace(
                            ['{{first_name}}', '{{last_name}}', '{{birthday}}', '\n'],
                            [$record->first_name, $record->last_name, $record->birthday->format('d F'), "\n"],
                            $greeting->message
                        );

                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer u489f486268ed444.f51e76d509f94b93855bb8bc61521f93'
                        ])->post('http://46.102.156.214:3001/api/v1/messages', [
                            'recipient_type' => 'individual',
                            'to' => $record->whatsapp,
                            'type' => 'text',
                            'text' => [
                                'body' => $message
                            ]
                        ]);

                        BirthdayLog::create([
                            'id_profile' => $record->id_user,
                            'status_sent' => $response->status() === 200 ? 1 : 0,
                            'date_sent' => now()
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
                        'x-bind:class' => "{ 'animate-spin': spinning }"
                    ])
            ])
            ->heading('Ulang Tahun Hari Ini')
            ->defaultSort('first_name')
            ->paginated(false);
    }
}
