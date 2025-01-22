<?php

namespace App\Filament\Pages;

use App\Models\AnggotaReferral;
use App\Models\WithdrawalKomisi;
use App\Models\TransaksiReferral;
use App\Models\SettingKomisi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class PenarikanKomisi extends Page implements HasForms
{
    use InteractsWithForms, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.penarikan-komisi';
    protected static ?string $navigationGroup = 'Referral';
    protected static ?string $navigationLabel = 'Penarikan Komisi';

    public ?array $data = [];
    public $anggotaReferral = null;
    public $sisaKomisi = 0;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_referral')
                    ->label('Anggota Referral')
                    ->options(AnggotaReferral::query()
                        ->withSum(['transaksiReferral as total_komisi' => function ($query) {
                            $query->where('status_komisi', 'approved');
                        }], 'nilai_komisi')
                        ->withSum(['transaksiReferral as total_withdrawal' => function ($query) {
                            $query->where('jenis_transaksi', 'withdrawal');
                        }], 'nilai_withdrawal')
                        ->get()
                        ->mapWithKeys(function ($anggota) {
                            $sisaKomisi = ($anggota->total_komisi ?? 0) - ($anggota->total_withdrawal ?? 0);
                            return [$anggota->id_referral => "{$anggota->nama} (Sisa Komisi: Rp " . number_format($sisaKomisi, 0, ',', '.') . ")"];
                        }))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $anggota = AnggotaReferral::query()
                                ->withSum(['transaksiReferral as total_komisi' => function ($query) {
                                    $query->where('status_komisi', 'approved');
                                }], 'nilai_komisi')
                                ->withSum(['transaksiReferral as total_withdrawal' => function ($query) {
                                    $query->where('jenis_transaksi', 'withdrawal');
                                }], 'nilai_withdrawal')
                                ->find($state);

                            $this->sisaKomisi = ($anggota->total_komisi ?? 0) - ($anggota->total_withdrawal ?? 0);
                            $this->anggotaReferral = $anggota;
                        }
                    })
                    ->required(),

                TextInput::make('nilai_withdrawal')
                    ->label('Jumlah Penarikan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->rules([
                        fn () => function (string $attribute, $value, \Closure $fail) {
                            if ($value > $this->sisaKomisi) {
                                $fail("Jumlah penarikan tidak boleh melebihi sisa komisi (Rp " . number_format($this->sisaKomisi, 0, ',', '.') . ")");
                            }
                        },
                    ]),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        // Ambil kode_komisi pertama dari setting_komisi
        $kodeKomisi = SettingKomisi::first()->kode_komisi;

        $transaksi = new TransaksiReferral();
        $transaksi->id_referral = $data['id_referral'];
        $transaksi->id_nasabah = $this->anggotaReferral->id_user; // Menggunakan id_user dari anggota referral
        $transaksi->kode_komisi = $kodeKomisi; // Menggunakan kode_komisi yang valid
        $transaksi->nominal_transaksi = $data['nilai_withdrawal'];
        $transaksi->nilai_withdrawal = $data['nilai_withdrawal'];
        $transaksi->keterangan = $data['keterangan'];
        $transaksi->jenis_transaksi = 'withdrawal';
        $transaksi->tanggal_transaksi = now();
        $transaksi->status_komisi = 'pending';
        $transaksi->save();

        Notification::make()
            ->title('Berhasil melakukan penarikan komisi')
            ->success()
            ->send();

        $this->form->fill();
    }
}
