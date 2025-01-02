<?php

namespace App\Filament\Pages;

use App\Models\Karyawan;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendBulkWhatsAppMessage;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;

class KirimWAKaryawan extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static ?string $navigationGroup = 'Data Karyawan';
    protected static ?string $navigationLabel = 'Kirim WA Karyawan';
    protected static string $view = 'filament.pages.kirim-w-a-karyawan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->label('Pesan WhatsApp')
                    ->required()
                    ->hint('Variabel yang tersedia: {first_name}, {last_name}')
                    ->rows(5)
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $activeKaryawan = Karyawan::query()
            ->where('is_active', 1)
            ->whereNotNull('no_telepon')
            ->get();

        if ($activeKaryawan->isEmpty()) {
            Notification::make()
                ->title('Error')
                ->body('Tidak ada nomor WhatsApp karyawan aktif yang ditemukan')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            SendBulkWhatsAppMessage::dispatch($data['message'], $activeKaryawan);

            DB::commit();

            Notification::make()
                ->title('Berhasil')
                ->body('Pesan WhatsApp massal telah dijadwalkan untuk dikirim')
                ->success()
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan saat mengirim pesan WhatsApp massal')
                ->danger()
                ->send();
        }
    }
}
