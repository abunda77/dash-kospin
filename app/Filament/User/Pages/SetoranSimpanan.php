<?php

namespace App\Filament\User\Pages;

use App\Enums\StatusSetoran;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Services\BuatSetoranTabunganService;
use App\Services\KirimKlaimPembayaranService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SetoranSimpanan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Setoran Simpanan';

    protected static ?string $title = 'Setoran Simpanan via QRIS';

    protected static string $view = 'filament.user.pages.setoran-simpanan';

    protected static ?int $navigationSort = 15;

    public ?array $generateData = [];

    public ?array $claimData = [];

    public function mount(): void
    {
        $this->generateForm->fill();
        $this->claimForm->fill([
            'waktu_klaim_bayar' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    protected function getForms(): array
    {
        return [
            'generateForm',
            'claimForm',
        ];
    }

    public function generateForm(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Select::make('id_tabungan')
                    ->label('Rekening Tabungan Tujuan')
                    ->options(fn () => Tabungan::query()
                        ->whereHas('profile', fn ($query) => $query
                            ->where('id_user', $user->getKey())
                            ->where('is_active', true))
                        ->where('status_rekening', 'aktif')
                        ->with('produkTabungan')
                        ->get()
                        ->mapWithKeys(fn ($tabungan) => [
                            $tabungan->id => "{$tabungan->no_tabungan} - ".($tabungan->produkTabungan->nama_produk ?? 'Tabungan').' (Saldo: Rp '.number_format($tabungan->saldo_akhir, 0, ',', '.').')',
                        ]))
                    ->placeholder('Pilih rekening tabungan aktif')
                    ->required(),

                Radio::make('preset_jumlah')
                    ->label('Pilih Nominal Setoran')
                    ->options([
                        '10000' => 'Rp 10.000',
                        '25000' => 'Rp 25.000',
                        '50000' => 'Rp 50.000',
                        '100000' => 'Rp 100.000',
                        '250000' => 'Rp 250.000',
                        '500000' => 'Rp 500.000',
                        'custom' => 'Nominal Kustom',
                    ])
                    ->default('50000')
                    ->live()
                    ->required(),

                TextInput::make('custom_jumlah')
                    ->label('Nominal Kustom')
                    ->numeric()
                    ->postfix('Rupiah')
                    ->placeholder('Masukkan nominal minimal Rp 10.000')
                    ->visible(fn ($get) => $get('preset_jumlah') === 'custom')
                    ->required(fn ($get) => $get('preset_jumlah') === 'custom')
                    ->minValue(10000)
                    ->maxValue(100000000),
            ])
            ->statePath('generateData');
    }

    public function claimForm(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('waktu_klaim_bayar')
                    ->label('Waktu Bayar')
                    ->default(now())
                    ->required(),

                TextInput::make('nama_pembayar')
                    ->label('Nama Pengirim / Pembayar')
                    ->placeholder('Nama sesuai pada rekening pengirim')
                    ->required(),

                TextInput::make('referensi_pembayaran')
                    ->label('Nomor Referensi (Opsional)')
                    ->placeholder('Contoh: Ref-123456...'),

                FileUpload::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran (Opsional)')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(4096)
                    ->storeFiles(false),

                Textarea::make('catatan_pengguna')
                    ->label('Catatan Tambahan (Opsional)')
                    ->placeholder('Tulis pesan atau catatan revisi di sini...'),
            ])
            ->statePath('claimData');
    }

    public function generateQris(): void
    {
        try {
            $data = $this->generateForm->getState();
            $jumlah = $data['preset_jumlah'] === 'custom'
                ? (int) $data['custom_jumlah']
                : (int) $data['preset_jumlah'];

            $user = Auth::user();
            $tabungan = Tabungan::query()
                ->whereKey($data['id_tabungan'])
                ->where('status_rekening', 'aktif')
                ->whereHas('profile', fn ($query) => $query
                    ->where('id_user', $user->getKey())
                    ->where('is_active', true))
                ->first();

            if (! $tabungan) {
                throw new \InvalidArgumentException('Rekening tidak valid atau tidak aktif.');
            }

            $buatService = app(BuatSetoranTabunganService::class);
            $buatService->execute($user, $tabungan, $jumlah);

            $this->generateForm->fill();

            Notification::make()
                ->title('Sukses')
                ->body('QRIS berhasil di-generate.')
                ->success()
                ->send();
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            Notification::make()
                ->title('Proses Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Kesalahan')
                ->body('Terjadi kesalahan pada sistem. Silakan hubungi admin.')
                ->danger()
                ->send();
        }
    }

    public function claimPayment(int $setoranId): void
    {
        try {
            $data = $this->claimForm->getState();
            $user = Auth::user();
            $setoran = SetoranTabungan::findOrFail($setoranId);

            if ($setoran->user_id !== $user->id) {
                throw new \RuntimeException('Akses ditolak.');
            }

            $uploadedFile = $data['bukti_pembayaran'] ?? null;

            if ($uploadedFile && ! $uploadedFile instanceof UploadedFile) {
                throw new \InvalidArgumentException('Bukti pembayaran tidak valid.');
            }

            $waktuKlaim = Carbon::parse($data['waktu_klaim_bayar']);
            $klaimService = app(KirimKlaimPembayaranService::class);

            $klaimService->execute(
                $user,
                $setoran,
                $waktuKlaim,
                $data['nama_pembayar'],
                $data['referensi_pembayaran'] ?? null,
                $data['catatan_pengguna'] ?? null,
                $uploadedFile
            );

            $this->claimForm->fill([
                'waktu_klaim_bayar' => now()->format('Y-m-d H:i:s'),
            ]);

            Notification::make()
                ->title('Sukses')
                ->body('Konfirmasi pembayaran berhasil dikirim.')
                ->success()
                ->send();
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            Notification::make()
                ->title('Proses Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Kesalahan')
                ->body('Gagal memverifikasi bukti. Silakan periksa kembali berkas Anda.')
                ->danger()
                ->send();
        }
    }

    public function getActiveSetoran()
    {
        $user = Auth::user();

        return SetoranTabungan::where('user_id', $user->id)
            ->whereIn('status', [
                StatusSetoran::MENUNGGU_PEMBAYARAN,
                StatusSetoran::MENUNGGU_VERIFIKASI,
                StatusSetoran::SEDANG_DIPERIKSA,
                StatusSetoran::PERLU_REVISI,
                StatusSetoran::DISETUJUI,
            ])
            ->with('tabungan')
            ->first();
    }

    public function getHistorySetoran()
    {
        $user = Auth::user();

        return SetoranTabungan::where('user_id', $user->id)
            ->with('tabungan')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
