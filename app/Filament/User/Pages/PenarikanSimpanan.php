<?php

namespace App\Filament\User\Pages;

use App\Enums\StatusPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\Tabungan;
use App\Services\BatalkanPenarikanService;
use App\Services\BuatPenarikanTabunganService;
use App\Services\KirimRevisiPenarikanService;
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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Throwable;

class PenarikanSimpanan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Penarikan Simpanan';

    protected static ?string $title = 'Penarikan Simpanan';

    protected static string $view = 'filament.user.pages.penarikan-simpanan';

    protected static ?int $navigationSort = 16;

    public static function getNavigationGroup(): ?string
    {
        return 'Simpanan';
    }

    public ?array $createData = [];

    public ?array $revisiData = [];

    public function mount(): void
    {
        $this->createForm->fill();
        $this->revisiForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'createForm',
            'revisiForm',
        ];
    }

    public function createForm(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Select::make('id_tabungan')
                    ->label('Rekening Tabungan Sumber')
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
                    ->label('Pilih Nominal Penarikan')
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

                Select::make('bank')
                    ->label('Bank')
                    ->options([
                        'BRI' => 'Bank Rakyat Indonesia (BRI)',
                        'BNI' => 'Bank Negara Indonesia (BNI)',
                        'BCA' => 'Bank Central Asia (BCA)',
                        'MANDIRI' => 'Bank Mandiri',
                        'BSI' => 'Bank Syariah Indonesia (BSI)',
                        'BTPN' => 'Bank BTPN',
                        'LAINNYA' => 'Bank Lainnya',
                    ])
                    ->placeholder('Pilih bank tujuan')
                    ->searchable()
                    ->required(),

                TextInput::make('nama_bank')
                    ->label('Nama Bank')
                    ->placeholder('Contoh: BRI Unit Kota / BCA KCU Sudirman')
                    ->required(),

                TextInput::make('nama_nasabah')
                    ->label('Nama Nasabah')
                    ->placeholder('Nama sesuai pada rekening bank tujuan')
                    ->required(),

                TextInput::make('referensi_penarikan')
                    ->label('Nomor Referensi (Opsional)')
                    ->placeholder('Contoh: Ref-123456...'),

                FileUpload::make('bukti_penarikan')
                    ->label('Dokumen Pendukung (Opsional)')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(4096)
                    ->storeFiles(false),

                Textarea::make('catatan_pengguna')
                    ->label('Catatan Tambahan (Opsional)')
                    ->placeholder('Tulis pesan atau catatan di sini...'),
            ])
            ->statePath('createData');
    }

    public function revisiForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('referensi_penarikan')
                    ->label('Nomor Referensi (Opsional)')
                    ->placeholder('Contoh: Ref-123456...'),

                FileUpload::make('bukti_penarikan')
                    ->label('Dokumen Pendukung (Opsional)')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(4096)
                    ->storeFiles(false),

                Textarea::make('catatan_pengguna')
                    ->label('Catatan Tambahan (Opsional)')
                    ->placeholder('Tulis pesan atau catatan revisi di sini...'),
            ])
            ->statePath('revisiData');
    }

    public function ajukanPenarikan(): void
    {
        try {
            $data = $this->createForm->getState();
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

            $uploadedFile = $data['bukti_penarikan'] ?? null;

            if ($uploadedFile && ! $uploadedFile instanceof UploadedFile) {
                throw new \InvalidArgumentException('Dokumen pendukung tidak valid.');
            }

            $buatService = app(BuatPenarikanTabunganService::class);
            $buatService->execute(
                $user,
                $tabungan,
                $jumlah,
                $data['bank'],
                $data['nama_bank'],
                $data['nama_nasabah'],
                $data['referensi_penarikan'] ?? null,
                $data['catatan_pengguna'] ?? null,
                $uploadedFile
            );

            $this->createForm->fill();

            Notification::make()
                ->title('Sukses')
                ->body('Permohonan penarikan berhasil diajukan dan menunggu verifikasi.')
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

    public function kirimRevisi(int $penarikanId): void
    {
        try {
            $data = $this->revisiForm->getState();
            $user = Auth::user();
            $penarikan = PenarikanTabungan::findOrFail($penarikanId);

            if ($penarikan->user_id !== $user->id) {
                throw new \RuntimeException('Akses ditolak.');
            }

            $uploadedFile = $data['bukti_penarikan'] ?? null;

            if ($uploadedFile && ! $uploadedFile instanceof UploadedFile) {
                throw new \InvalidArgumentException('Dokumen pendukung tidak valid.');
            }

            $revisiService = app(KirimRevisiPenarikanService::class);
            $revisiService->execute(
                $user,
                $penarikan,
                $data['referensi_penarikan'] ?? null,
                $data['catatan_pengguna'] ?? null,
                $uploadedFile
            );

            $this->revisiForm->fill();

            Notification::make()
                ->title('Sukses')
                ->body('Revisi penarikan berhasil dikirim.')
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
                ->body('Gagal memverifikasi berkas. Silakan periksa kembali berkas Anda.')
                ->danger()
                ->send();
        }
    }

    public function cancelPenarikan(int $penarikanId): void
    {
        try {
            $user = Auth::user();
            $penarikan = PenarikanTabungan::query()
                ->where('user_id', $user->id)
                ->findOrFail($penarikanId);

            Gate::authorize('batalkan', $penarikan);
            app(BatalkanPenarikanService::class)->execute($user, $penarikan);

            Notification::make()
                ->title('Penarikan Dibatalkan')
                ->body('Penarikan berhasil dibatalkan. Anda dapat membuat pengajuan baru.')
                ->success()
                ->send();
        } catch (AuthorizationException|ModelNotFoundException|\RuntimeException $e) {
            Notification::make()
                ->title('Pembatalan Gagal')
                ->body($e instanceof ModelNotFoundException
                    ? 'Data penarikan tidak ditemukan.'
                    : $e->getMessage())
                ->danger()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Kesalahan')
                ->body('Penarikan gagal dibatalkan. Silakan coba kembali.')
                ->danger()
                ->send();
        }
    }

    public function getActivePenarikan()
    {
        $user = Auth::user();

        return PenarikanTabungan::where('user_id', $user->id)
            ->whereIn('status', [
                StatusPenarikan::MENUNGGU_VERIFIKASI,
                StatusPenarikan::SEDANG_DIPERIKSA,
                StatusPenarikan::PERLU_REVISI,
                StatusPenarikan::DISETUJUI,
            ])
            ->with('tabungan')
            ->first();
    }

    public function getHistoryPenarikan()
    {
        $user = Auth::user();

        return PenarikanTabungan::where('user_id', $user->id)
            ->with('tabungan')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
