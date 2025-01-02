<?php

namespace App\Filament\Pages;

use App\Jobs\SendBulkProfileEmail;
use App\Models\Profile;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class KirimEmailMassal extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Promotion';
    protected static ?string $navigationLabel = 'Kirim Email Massal';
    protected static string $view = 'filament.pages.kirim-email-massal';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('subject')
                    ->label('Subjek Email')
                    ->required()
                    ->maxLength(255)
                    ->hint('Variabel yang tersedia: {first_name}, {last_name}'),
                RichEditor::make('message')
                    ->label('Isi Pesan')
                    ->required()
                    ->hint('Variabel yang tersedia: {first_name}, {last_name}')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'orderedList',
                        'unorderedList',
                        'redo',
                        'undo',
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $activeProfiles = Profile::query()
            ->where('is_active', 1)
            ->whereNotNull('email')
            ->get();

        if ($activeProfiles->isEmpty()) {
            Notification::make()
                ->title('Error')
                ->body('Tidak ada penerima email aktif yang ditemukan')
                ->danger()
                ->send();
            return;
        }

        $mailData = [
            'subject' => $data['subject'],
            'message' => $data['message']
        ];

        try {
            DB::beginTransaction();

            SendBulkProfileEmail::dispatch($mailData, $activeProfiles);

            DB::commit();

            Notification::make()
                ->title('Berhasil')
                ->body('Email massal telah dijadwalkan untuk dikirim')
                ->success()
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan saat mengirim email massal')
                ->danger()
                ->send();
        }
    }
}
