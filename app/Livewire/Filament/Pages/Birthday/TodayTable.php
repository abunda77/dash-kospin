<?php

namespace App\Livewire\Filament\Pages\Birthday;

use App\Models\Profile;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Models\BirthdayLog;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use App\Models\BirthdayGreeting;

class TodayTable extends \Filament\Tables\TableComponent
{
    use InteractsWithTable;

    private function formatWhatsAppNumber(string $number): string
    {
        // Hapus karakter non-digit
        $number = preg_replace('/[^0-9]/', '', $number);

        // Hapus awalan 0, +62, atau 62
        $number = preg_replace('/^(\+62|62|0)/', '', $number);

        // Pastikan nomor dimulai dengan 62
        return '62' . $number;
    }

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
                    ->label('Full Name')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
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

                        $whatsapp = $this->formatWhatsAppNumber($record->whatsapp);

                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer u489f486268ed444.f51e76d509f94b93855bb8bc61521f93'
                        ])->post('http://46.102.156.214:3001/api/v1/messages', [
                            'recipient_type' => 'individual',
                            'to' => $whatsapp,
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
