<?php

namespace App\Filament\Pages;

use App\Jobs\SendProfileEmail;
use App\Models\Profile;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class KirimEmail extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Promotion';
    protected static ?string $navigationLabel = 'Kirim Email';
    protected static string $view = 'filament.pages.kirim-email';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('profile_id')
                    ->label('Pilih Penerima')
                    ->options(
                        Profile::query()
                            ->whereNotNull('email')
                            ->pluck('email', 'id_user')
                    )
                    ->searchable()
                    ->required(),
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

        $profile = Profile::find($data['profile_id']);

        if (!$profile || !$profile->email) {
            Notification::make()
                ->title('Error')
                ->body('Email penerima tidak valid')
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

            SendProfileEmail::dispatch($mailData, $profile->email, $profile->id_user);

            DB::commit();

            Notification::make()
                ->title('Berhasil')
                ->body('Email telah dijadwalkan untuk dikirim')
                ->success()
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan saat mengirim email')
                ->danger()
                ->send();
        }
    }
}
