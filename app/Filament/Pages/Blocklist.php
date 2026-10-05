<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Blocklist extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Blocklist';

    protected static string $view = 'filament.pages.blocklist';

    public ?array $data = [];

    public function mount(): void
    {
        $path = (string) config('security.blacklist_path');

        if (! File::isFile($path) || ! File::isReadable($path)) {
            Notification::make()->title('File blacklist tidak dapat dibaca.')->danger()->send();

            return;
        }

        $this->form->fill(['content' => File::get($path)]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('content')
                    ->label('Isi blacklist.txt')
                    ->helperText('Satu entri per baris: email:domain.com, email:*@domain.com, atau ip:alamat-IP. Baris # adalah komentar.')
                    ->rows(20)
                    ->rules([
                        fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                            foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $index => $line) {
                                $line = trim($line);

                                if ($line === '' || str_starts_with($line, '#')) {
                                    continue;
                                }

                                [$type, $entry] = array_pad(explode(':', $line, 2), 2, '');
                                $entry = trim($entry);
                                $domain = str_starts_with($entry, '*@') ? substr($entry, 2) : $entry;

                                if (($type === 'email' && filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && str_contains($domain, '.'))
                                    || ($type === 'ip' && filter_var($entry, FILTER_VALIDATE_IP))) {
                                    continue;
                                }

                                $fail('Format tidak valid pada baris '.($index + 1).'. Gunakan email:domain.com atau ip:alamat-IP.');
                                break;
                            }
                        },
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $content = (string) ($this->form->getState()['content'] ?? '');
        $path = (string) config('security.blacklist_path');

        if (! File::isFile($path) || ! File::isWritable($path)) {
            Notification::make()->title('File blacklist tidak dapat ditulis.')->danger()->send();

            return;
        }

        $oldSignature = md5($path.'|'.(@filemtime($path) ?: 0).'|'.(@filesize($path) ?: 0));

        if (File::put($path, $content, true) === false) {
            Notification::make()->title('Gagal menyimpan blacklist.')->danger()->send();

            return;
        }

        clearstatcache(true, $path);
        $newSignature = md5($path.'|'.(@filemtime($path) ?: 0).'|'.(@filesize($path) ?: 0));
        Cache::forget('security.blacklist:'.$oldSignature);
        Cache::forget('security.blacklist:'.$newSignature);

        Notification::make()->title('Blocklist berhasil disimpan.')->success()->send();
    }
}
