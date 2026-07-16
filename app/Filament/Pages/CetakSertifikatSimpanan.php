<?php

namespace App\Filament\Pages;

use App\Models\Tabungan;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CetakSertifikatSimpanan extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Tabungan';

    protected static ?string $navigationLabel = 'Cetak Sertifikat Simpanan';

    protected static ?string $title = 'Cetak Sertifikat Simpanan';

    protected static ?string $slug = 'cetak-sertifikat-simpanan';

    protected static string $view = 'filament.pages.cetak-sertifikat-simpanan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pilih Rekening Simpanan')
                    ->schema([
                        Select::make('tabungan_id')
                            ->label('No. Tabungan')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => $this->getTabunganOptions($search))
                            ->getOptionLabelUsing(fn ($value): ?string => $this->getTabunganOptionLabel($value))
                            ->live()
                            ->afterStateUpdated(fn ($state) => $this->fillInformasiRekening($state))
                            ->required(),
                    ]),
                Section::make('Informasi Rekening')
                    ->schema([
                        TextInput::make('nama_produk')
                            ->label('Produk Tabungan')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('no_sertifikat')
                            ->label('No. Sertifikat')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('nama')
                            ->label('Nama')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('no_hp')
                            ->label('No. HP')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('alamat')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('tanggal_pembukaan')
                            ->label('Tanggal Pembukaan Rekening')
                            ->displayFormat('d F Y')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('saldo')
                            ->label('Saldo')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('bunga')
                            ->label('Bunga (%)')
                            ->suffix('% p.a')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Informasi Rekening Bank')
                    ->schema([
                        TextInput::make('bank_atas_nama')
                            ->label('Atas Nama')
                            ->required(),
                        TextInput::make('bank_no_rekening')
                            ->label('No. Rekening')
                            ->required(),
                        TextInput::make('bank_nama_bank')
                            ->label('Nama Bank')
                            ->required(),
                    ])
                    ->columns(3),
                Section::make('Kontrak')
                    ->schema([
                        Select::make('jangka_waktu')
                            ->label('Jangka Waktu Kontrak')
                            ->options([
                                '3' => '3 Bulan',
                                '6' => '6 Bulan',
                                '12' => '12 Bulan',
                                '24' => '24 Bulan',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state): void {
                                $tanggalBuka = $this->data['tanggal_pembukaan'] ?? null;
                                if ($tanggalBuka && $state) {
                                    $this->data['akhir_kontrak'] = Carbon::parse($tanggalBuka)
                                        ->addMonths((int) $state)
                                        ->format('Y-m-d');
                                } else {
                                    $this->data['akhir_kontrak'] = null;
                                }
                            })
                            ->required(),
                        DatePicker::make('akhir_kontrak')
                            ->label('Akhir Kontrak')
                            ->displayFormat('d F Y')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function cetak(): StreamedResponse
    {
        $data = $this->form->getState();
        $tabungan = Tabungan::query()
            ->with(['profile', 'produkTabungan.beayaTabungan'])
            ->findOrFail($data['tabungan_id']);

        $jangkaWaktu = (int) $data['jangka_waktu'];
        $akhirKontrak = Carbon::parse($tabungan->tanggal_buka_rekening)->addMonths($jangkaWaktu);

        $pdf = Pdf::loadView('pdf.sertifikat-simpanan-a5', [
            'tabungan' => $tabungan,
            'jangkaWaktu' => $jangkaWaktu,
            'akhirKontrak' => $akhirKontrak,
            'bankAtasNama' => $data['bank_atas_nama'],
            'bankNoRekening' => $data['bank_no_rekening'],
            'bankNamaBank' => $data['bank_nama_bank'],
        ])->setPaper('a5', 'landscape');

        $filename = 'sertifikat-simpanan-'.Str::slug($tabungan->no_tabungan).'.pdf';

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $filename);
    }

    private function getTabunganOptions(string $search): array
    {
        return Tabungan::query()
            ->with(['profile', 'produkTabungan.beayaTabungan'])
            ->where(function (Builder $query) use ($search): void {
                $query->where('no_tabungan', 'like', "%{$search}%")
                    ->orWhereHas('profile', function (Builder $profileQuery) use ($search): void {
                        $profileQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            })
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Tabungan $tabungan): array => [
                $tabungan->getKey() => $this->formatTabunganOptionLabel($tabungan),
            ])
            ->all();
    }

    private function getTabunganOptionLabel($value): ?string
    {
        $tabungan = Tabungan::query()
            ->with(['profile', 'produkTabungan.beayaTabungan'])
            ->find($value);

        return $tabungan ? $this->formatTabunganOptionLabel($tabungan) : null;
    }

    private function formatTabunganOptionLabel(Tabungan $tabungan): string
    {
        $nama = trim(($tabungan->profile?->first_name ?? '').' '.($tabungan->profile?->last_name ?? ''));
        $produk = $tabungan->produkTabungan?->nama_produk ?? '-';

        return "{$tabungan->no_tabungan} - {$nama} ({$produk})";
    }

    private function fillInformasiRekening($tabunganId): void
    {
        $tabungan = Tabungan::query()
            ->with(['profile', 'produkTabungan.beayaTabungan'])
            ->find($tabunganId);

        $bunga = $tabungan?->produkTabungan?->beayaTabungan?->persentase_bunga;

        $this->data = array_merge($this->data ?? [], [
            'tabungan_id' => $tabungan?->getKey(),
            'nama_produk' => $tabungan?->produkTabungan?->nama_produk,
            'no_sertifikat' => $tabungan?->no_tabungan,
            'nama' => $tabungan ? trim(($tabungan->profile?->first_name ?? '').' '.($tabungan->profile?->last_name ?? '')) : null,
            'alamat' => $tabungan?->profile?->address,
            'no_hp' => $tabungan?->profile?->phone,
            'tanggal_pembukaan' => $tabungan?->tanggal_buka_rekening?->format('Y-m-d'),
            'saldo' => $tabungan ? number_format((float) $tabungan->saldo, 0, ',', '.') : null,
            'bunga' => $bunga !== null ? number_format((float) $bunga, 2) : null,
            'jangka_waktu' => null,
            'akhir_kontrak' => null,
        ]);
    }
}
