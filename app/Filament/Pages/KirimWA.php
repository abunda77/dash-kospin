<?php

namespace App\Filament\Pages;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Karyawan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class KirimWA extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static ?string $navigationGroup = 'Data Karyawan';
    protected static ?string $navigationLabel = 'Kirim WA Personal';
    protected static string $view = 'filament.pages.kirim-w-a';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('karyawan_id')
                    ->label('Pilih Penerima')
                    ->options(
                        Karyawan::query()
                            ->where('is_active', 1)
                            ->whereNotNull('no_telepon')
                            ->get()
                            ->mapWithKeys(function ($karyawan) {
                                return [$karyawan->id => $karyawan->nama . ' (' . $karyawan->no_telepon . ')'];
                            })
                    )
                    ->searchable()
                    ->required(),
                Textarea::make('message')
                    ->label('Pesan WhatsApp')
                    ->required()
                    ->hint('Variabel yang tersedia: {nama}, {nik_karyawan}')
                    ->rows(5),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $karyawan = Karyawan::find($data['karyawan_id']);

        if (!$karyawan || !$karyawan->no_telepon) {
            Notification::make()
                ->title('Error')
                ->body('Nomor WhatsApp penerima tidak valid')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            SendWhatsAppMessage::dispatch($data['message'], $karyawan);

            DB::commit();

            Notification::make()
                ->title('Berhasil')
                ->body('Pesan WhatsApp telah dijadwalkan untuk dikirim')
                ->success()
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan saat mengirim pesan WhatsApp')
                ->danger()
                ->send();
        }
    }
}
