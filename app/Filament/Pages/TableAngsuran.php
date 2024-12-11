<?php

namespace App\Filament\Pages;

use App\Models\Pinjaman;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\TransaksiPinjaman;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Actions\Column as ActionsColumn;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class TableAngsuran extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
            }
    protected static string $view = 'filament.pages.table-angsuran';

    public ?string $noPinjaman = '';
    public $pinjaman = null;
    public $angsuranList = [];
    public $showPaymentForm = false;
    public $selectedPeriod = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('noPinjaman')
                    ->label('No Pinjaman')
                    ->required(),

                Grid::make(2)
                    ->schema([
                        DatePicker::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->visible(fn() => $this->showPaymentForm),

                        TextInput::make('total_pembayaran')
                            ->label('Total Pembayaran')
                            ->disabled()
                            ->visible(fn() => $this->showPaymentForm),
                    ])
                    ->visible(fn() => $this->showPaymentForm)
            ]);
    }

    public function search(): void
    {
        $this->pinjaman = Pinjaman::with(['profile', 'produkPinjaman', 'biayaBungaPinjaman'])
            ->where('no_pinjaman', $this->noPinjaman)
            ->first();

        if ($this->pinjaman) {
            $this->calculateAngsuran();
            Notification::make()
                ->title('Data ditemukan')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Data tidak ditemukan')
                ->danger()
                ->send();
        }
    }

    public function clearSearch(): void
    {
        $this->noPinjaman = '';
        $this->pinjaman = null;
        $this->angsuranList = [];
        $this->form->fill();
    }

    private function calculateAngsuran(): void
    {
        $pokok = $this->pinjaman->jumlah_pinjaman;
        $bungaPerTahun = $this->pinjaman->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $this->pinjaman->jangka_waktu;

        // Hitung bunga per bulan (total bunga setahun dibagi jangka waktu)
        $bungaPerBulan = ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;

        // Hitung angsuran pokok per bulan
        $angsuranPokok = $pokok / $jangkaWaktu;

        // Total angsuran per bulan (tetap)
        $totalAngsuran = $angsuranPokok + $bungaPerBulan;

        $this->angsuranList = [];
        $sisaPokok = $pokok;

        // Ambil tanggal awal pinjaman
        $tanggalJatuhTempo = $this->pinjaman->tanggal_pinjaman->copy();

        for ($i = 1; $i <= $jangkaWaktu; $i++) {
            // Tambah 1 bulan untuk tanggal jatuh tempo berikutnya
            $tanggalJatuhTempo = $tanggalJatuhTempo->addMonth();

            $this->angsuranList[] = [
                'periode' => $i,
                'pokok' => $angsuranPokok,
                'bunga' => $bungaPerBulan,
                'angsuran' => $totalAngsuran,
                'sisa_pokok' => $sisaPokok - $angsuranPokok,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('d/m/Y')
            ];

            $sisaPokok -= $angsuranPokok;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransaksiPinjaman::query()
                    ->where('pinjaman_id', optional($this->pinjaman)->id_pinjaman)
            )
            ->columns([
                TextColumn::make('angsuran_ke')
                    ->label('Angsuran Ke'),
                TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal Bayar')
                    ->date(),
                TextColumn::make('angsuran_pokok')
                    ->label('Pokok')
                    ->money('Rp.')
                    ->summarize([
                        Sum::make()
                            ->money('Rp.')
                    ]),
                TextColumn::make('angsuran_bunga')
                    ->label('Bunga')
                    ->money('Rp.')
                    ->summarize([
                        Sum::make()
                            ->money('Rp.')
                    ]),
                TextColumn::make('denda')
                    ->label('Denda')
                    ->money('Rp.')
                    ->summarize([
                        Sum::make()
                            ->money('Rp.')
                    ]),
                TextColumn::make('total_pembayaran')
                    ->label('Total')
                    ->money('Rp.')
                    ->summarize([
                        Sum::make()
                            ->money('Rp.')
                    ]),
                TextColumn::make('status_pembayaran')
                    ->label('Status'),
                Column::make('actions')
                    ->label('Aksi')
                    ->alignEnd()
                    ->view('filament.tables.columns.actions'),
                Column::make('cetak_invoice')
                    ->label('Cetak Invoice')
                    ->view('filament.tables.columns.invoice'),

            ])
            ->defaultSort('angsuran_ke', 'desc')
            ->poll('5s');
    }

    public function bayarAngsuran($periode)
    {
        // Cek periode sebelumnya
        $periodeSebelumnya = $periode - 1;
        if ($periodeSebelumnya > 0) {
            $pembayaranSebelumnya = TransaksiPinjaman::where('pinjaman_id', $this->pinjaman->id_pinjaman)
                ->where('angsuran_ke', $periodeSebelumnya)
                ->exists();

            if (!$pembayaranSebelumnya) {
                Notification::make()
                    ->title('Tidak dapat melakukan pembayaran')
                    ->body('Harap bayar angsuran periode sebelumnya terlebih dahulu')
                    ->danger()
                    ->send();
                return;
            }
        }

        $this->selectedPeriod = $periode;
        $this->showPaymentForm = true;

        // Ambil data angsuran yang akan dibayar
        $angsuran = $this->angsuranList[$periode - 1];

        // Konversi tanggal jatuh tempo dari format d/m/Y ke objek Carbon
        $tanggalJatuhTempo = Carbon::createFromFormat('d/m/Y', $angsuran['tanggal_jatuh_tempo'])->startOfDay();
        $tanggalBayar = Carbon::now()->startOfDay();

        $denda = 0;
        $hariTerlambat = 0;

        // Hitung hari terlambat jika tanggal bayar lebih besar dari tanggal jatuh tempo
        if ($tanggalBayar->gt($tanggalJatuhTempo)) {
            // Gunakan abs() untuk memastikan nilai positif
            $hariTerlambat = abs($tanggalJatuhTempo->diffInDays($tanggalBayar));

            // Ambil rate_denda dari relasi denda (dalam persen)
            $rateDenda = abs($this->pinjaman->denda->rate_denda);

            // Pastikan angsuran pokok positif
            $angsuranPokok = abs($angsuran['pokok']);

            // Hitung denda: (rate_denda * angsuran_pokok / 30) * hari_terlambat
            $denda = ($rateDenda/100 * $angsuranPokok / 30) * $hariTerlambat;
        }

        // Simpan transaksi dengan memastikan semua nilai positif
        TransaksiPinjaman::create([
            'pinjaman_id' => $this->pinjaman->id_pinjaman,
            'tanggal_pembayaran' => $tanggalBayar,
            'angsuran_ke' => $periode,
            'angsuran_pokok' => abs($angsuran['pokok']),
            'angsuran_bunga' => abs($angsuran['bunga']),
            'denda' => abs($denda),
            'total_pembayaran' => abs($angsuran['pokok']) + abs($angsuran['bunga']) + abs($denda),
            'sisa_pinjaman' => abs($angsuran['sisa_pokok']),
            'status_pembayaran' => 'LUNAS',
            'hari_terlambat' => $hariTerlambat
        ]);

        Notification::make()
            ->title('Pembayaran berhasil')
            ->success()
            ->send();

        $this->showPaymentForm = false;
    }

    public function deletePembayaran($id)
    {
        try {
            DB::beginTransaction();

            // Cari transaksi yang akan dihapus
            $transaksi = TransaksiPinjaman::findOrFail($id);

            // Update sisa pinjaman pada tabel pinjaman
            $pinjaman = Pinjaman::findOrFail($transaksi->pinjaman_id);
            $pinjaman->jumlah_pinjaman += $transaksi->angsuran_pokok; // Menggunakan jumlah_pinjaman sebagai pengganti sisa_pinjaman
            $pinjaman->jumlah_pinjaman -= $transaksi->angsuran_pokok;
            $pinjaman->save();

            // Hapus transaksi
            $transaksi->delete();

            DB::commit();

            Notification::make()
                ->title('Pembayaran berhasil dihapus')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Gagal menghapus pembayaran')
                ->danger()
                ->body('Terjadi kesalahan saat menghapus data: ' . $e->getMessage())
                ->send();
        }
    }

    public function isAngsuranPaid($periode): bool
    {
        // Jika tidak ada pinjaman, kembalikan false agar tombol tetap aktif
        if (!$this->pinjaman) {
            return false;
        }

        // Cek apakah ada history pembayaran untuk periode ini
        $pembayaran = TransaksiPinjaman::where('pinjaman_id', $this->pinjaman->id_pinjaman)
            ->where('angsuran_ke', $periode)
            ->exists();

        // Kembalikan true jika sudah ada pembayaran, false jika belum
        return $pembayaran;
    }

    public function print()
    {
        try {
            if (!$this->pinjaman) {
                Notification::make()
                    ->title('Silahkan cari data terlebih dahulu')
                    ->warning()
                    ->send();
                return;
            }

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');

            // Ambil data transaksi
            $transaksi = TransaksiPinjaman::where('pinjaman_id', $this->pinjaman->id_pinjaman)
                ->orderBy('angsuran_ke', 'asc')
                ->get();

            // Generate HTML
            $html = view('pdf.angsuran', [
                'pinjaman' => $this->pinjaman,
                'transaksi' => $transaksi,
                'angsuranList' => $this->angsuranList
            ])->render();

            $dompdf->loadHtml($html);
            $dompdf->render();

            $filename = $this->generatePdfFilename();

            return response()->streamDownload(
                fn () => print($dompdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            Log::error('Error in print: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan saat mencetak')
                ->danger()
                ->send();
            return null;
        }
    }

    private function generatePdfFilename()
    {
        return 'angsuran_' . $this->noPinjaman . '_' . date('Y-m-d_H-i-s') . '.pdf';
    }

    public function printSimulasi()
    {
        try {
            if (!$this->pinjaman) {
                Notification::make()
                    ->title('Silahkan cari data terlebih dahulu')
                    ->warning()
                    ->send();
                return;
            }

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');

            // Generate HTML
            $html = view('pdf.simulasi-angsuran', [
                'pinjaman' => $this->pinjaman,
                'angsuranList' => $this->angsuranList
            ])->render();

            $dompdf->loadHtml($html);
            $dompdf->render();

            $filename = $this->generateSimulasiPdfFilename();

            return response()->streamDownload(
                fn () => print($dompdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            Log::error('Error in printSimulasi: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan saat mencetak simulasi')
                ->danger()
                ->send();
            return null;
        }
    }

    private function generateSimulasiPdfFilename()
    {
        return 'simulasi_angsuran_' . $this->noPinjaman . '_' . date('Y-m-d_H-i-s') . '.pdf';
    }

    public function cetakInvoice(TransaksiPinjaman $record)
{
    try {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        $html = view('pdf.invoice', [
            'nama' => $record->pinjaman->profile->first_name . ' ' . $record->pinjaman->profile->last_name,
            'no_pinjaman' => $record->pinjaman->no_pinjaman,
            'angsuran_ke' => $record->angsuran_ke,
            'angsuran_pokok' => $record->angsuran_pokok,
            'denda' => $record->denda,
            'total_pembayaran' => $record->total_pembayaran,
            'tanggal_pembayaran' => $record->tanggal_pembayaran->format('d/m/Y'),
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = 'invoice_' . $record->angsuran_ke . '_' . date('Y-m-d_H-i-s') . '.pdf';

        return response()->streamDownload(
            fn () => print($dompdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    } catch (\Exception $e) {
        Log::error('Error generating invoice PDF: ' . $e->getMessage());
        Notification::make()
            ->title('Gagal mencetak Invoice')
            ->danger()
            ->body('Terjadi kesalahan: ' . $e->getMessage())
            ->send();
    }
}
}
