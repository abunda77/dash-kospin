<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Pinjaman;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Facades\Http;
use Dompdf\Dompdf;
use Dompdf\Options;

class ListKeterlambatanBulanIni extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'List Telat > 30 Hari';
    protected static ?string $title = 'List Telat Lebih Dari 30 Hari';

    protected static string $view = 'filament.pages.list-keterlambatan-bulan-ini';

    public static function getNavigationGroup(): ?string
    {
        return 'Pinjaman';
    }

    public static function getNavigationBadge(): ?string
    {
        $today = Carbon::today();
        $thirtyDaysAgo = $today->copy()->subDays(30);

        return Pinjaman::query()
            ->where('status_pinjaman', 'approved')
            ->where(function ($query) use ($today) {
                // Cek pinjaman yang memiliki transaksi
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 30', [$today]);
                })
                // ATAU pinjaman yang belum pernah bayar sama sekali
                ->orWhereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_pinjaman) > 30', [$today]);
                });
            })
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    private function getBaseQuery()
    {
        $today = Carbon::today();

        return Pinjaman::query()
            ->with(['profile', 'biayaBungaPinjaman', 'denda', 'transaksiPinjaman'])
            ->where('status_pinjaman', 'approved')
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->where(function ($query) use ($today) {
                // Ambil pinjaman yang tanggal jatuh temponya lebih dari 30 hari yang lalu
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 30', [$today]);
                })
                ->orWhereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                    // Untuk pinjaman yang belum pernah bayar sama sekali
                    $q->whereRaw('DATEDIFF(?, tanggal_pinjaman) > 30', [$today]);
                });
            });
    }

    // ... sisanya sama seperti ListKeterlambatan.php ...
    // Tambahkan method calculateAngsuranPokok, calculateHariTerlambat, calculateDenda, dll.
    public function getData()
    {
        return $this->getBaseQuery()->get();
    }

    private function calculateAngsuranPokok($record)
    {
        return $record->jumlah_pinjaman / $record->jangka_waktu;
    }

    private function calculateHariTerlambat($record, $today)
    {
        // Ambil tanggal jatuh tempo dari transaksi terakhir atau tanggal pinjaman
        $lastTransaction = $record->transaksiPinjaman()
            ->orderBy('angsuran_ke', 'desc')
            ->first();

        if ($lastTransaction) {
            // Jika ada transaksi sebelumnya, gunakan tanggal jatuh tempo berikutnya
            $tanggalJatuhTempo = Carbon::parse($lastTransaction->tanggal_pembayaran)
                ->addMonth()
                ->startOfDay();
        } else {
            // Jika belum ada transaksi, gunakan tanggal pinjaman + 1 bulan
            $tanggalJatuhTempo = Carbon::parse($record->tanggal_pinjaman)
                ->addMonth()
                ->startOfDay();
        }

        return $today->gt($tanggalJatuhTempo) ?
            $today->diffInDays($tanggalJatuhTempo) : 0;
    }

    private function calculateDenda($record, $angsuranPokok, $hariTerlambat)
    {
        return ($record->denda->rate_denda/100 * $angsuranPokok / 30) * $hariTerlambat;
    }

    private function formatWhatsAppNumber($whatsapp)
    {
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

        if (substr($whatsapp, 0, 1) === '0') {
            $whatsapp = '62' . substr($whatsapp, 1);
        }

        return $whatsapp;
    }

    private function calculateBungaPerBulan($record)
    {
        $pokok = $record->jumlah_pinjaman;
        $bungaPerTahun = $record->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $record->jangka_waktu;

        // Hitung bunga per bulan (total bunga setahun dibagi jangka waktu)
        return ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;
    }

    public function table(Table $table): Table
    {
        $today = Carbon::today();

        return $table
            ->query($this->getBaseQuery())
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('profile.first_name')
                    ->label('Nama')
                    ->formatStateUsing(fn ($record) =>
                        trim("{$record->profile->first_name} {$record->profile->last_name}")
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_pinjaman')
                    ->label('No Pinjaman')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jumlah_pinjaman')
                    ->label('Angsuran Pokok')
                    ->formatStateUsing(function ($record) {
                        $angsuranPokok = $this->calculateAngsuranPokok($record);
                        return 'Rp.' . number_format($angsuranPokok, 2, ',', '.');
                    }),

                TextColumn::make('beaya_bunga_pinjaman_id')
                    ->label('Bunga')
                    ->formatStateUsing(function ($record) {
                        $bunga = $this->calculateBungaPerBulan($record);
                        return 'Rp.' . number_format($bunga, 2, ',', '.');
                    }),

                TextColumn::make('denda_id')
                    ->label('Denda')
                    ->money('IDR')
                    ->formatStateUsing(function ($record) use ($today) {
                        try {
                            $angsuranPokok = $this->calculateAngsuranPokok($record);
                            $hariTerlambat = $this->calculateHariTerlambat($record, $today);
                            $denda = abs($this->calculateDenda($record, $angsuranPokok, $hariTerlambat));
                            return 'Rp.' . number_format($denda, 2, ',', '.');
                        } catch (\Exception $e) {
                            Log::error('Error calculating denda: ' . $e->getMessage());
                            return 'Rp.0,00';
                        }
                    }),

                TextColumn::make('jangka_waktu')
                    ->label('Total Bayar')
                    ->formatStateUsing(function ($record) use ($today) {
                        try {
                            $angsuranPokok = $this->calculateAngsuranPokok($record);
                            $bunga = $this->calculateBungaPerBulan($record);
                            $hariTerlambat = $this->calculateHariTerlambat($record, $today);
                            $denda = abs($this->calculateDenda($record, $angsuranPokok, $hariTerlambat));
                            $totalBayar = $angsuranPokok + $bunga + $denda;

                            return 'Rp.' . number_format($totalBayar, 2, ',', '.');
                        } catch (\Exception $e) {
                            Log::error('Error calculating total bayar: ' . $e->getMessage());
                            return 'Rp.0,00';
                        }
                    })
                    ->sortable(),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Hari Terlambat')
                    ->formatStateUsing(function ($record) use ($today) {
                        $hariTerlambat = $this->calculateHariTerlambat($record, $today);
                        return abs($hariTerlambat) . ' hari';
                    })
                    ->sortable(),

                TextColumn::make('profile.whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(function ($record) {
                        try {
                            $nama = trim("{$record->profile->first_name} {$record->profile->last_name}");
                            $pesan = urlencode("Halo {$nama}, ini adalah pengingat untuk pembayaran angsuran pinjaman Anda yang sudah jatuh tempo. Mohon segera melakukan pembayaran. Terima kasih.");
                            $whatsapp = $this->formatWhatsAppNumber($record->profile->whatsapp);
                            $url = "https://wa.me/{$whatsapp}?text={$pesan}";

                            return view('tables.columns.whatsapp-link', [
                                'url' => $url,
                                'whatsapp' => $record->profile->whatsapp
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error formatting WhatsApp link: ' . $e->getMessage());
                            return '-';
                        }
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('send_reminder')
                    ->label('Kirim Pengingat')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function ($record) {
                        $this->dispatch('spin-start');

                        $lastTransaction = $record->transaksiPinjaman()
                            ->orderBy('angsuran_ke', 'desc')
                            ->first();

                        $nama = trim("{$record->profile->first_name} {$record->profile->last_name}");
                        $angsuranPokok = $this->calculateAngsuranPokok($record);
                        $bunga = $this->calculateBungaPerBulan($record);
                        $hariTerlambat = $this->calculateHariTerlambat($record, Carbon::today());
                        $denda = abs($this->calculateDenda($record, $angsuranPokok, $hariTerlambat));
                        $totalBayar = $angsuranPokok + $bunga + $denda;

                        $message = "Halo {$nama},\n\n"
                            . "Ini adalah pengingat untuk pembayaran angsuran pinjaman Anda yang belum dibayar:\n\n"
                            . "No Pinjaman: {$record->no_pinjaman}\n"
                            . "Angsuran ke-" . ($lastTransaction ? ($lastTransaction->angsuran_ke + 1) : '1') . "\n"
                            . "Angsuran Pokok: Rp." . number_format($angsuranPokok, 2, ',', '.') . "\n"
                            . "Bunga: Rp." . number_format($bunga, 2, ',', '.') . "\n"
                            . "Denda: Rp." . number_format($denda, 2, ',', '.') . "\n"
                            . "Total Bayar: Rp." . number_format($totalBayar, 2, ',', '.') . "\n"
                            . "Keterlambatan: " . abs($hariTerlambat) . " hari\n\n"
                            . "Mohon segera melakukan pembayaran. Terima kasih.\n\n"
                            . "Salam,\n"
                            . "Koperasi SinaraArtha";

                        $whatsapp = $this->formatWhatsAppNumber($record->profile->whatsapp);

                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer u489f486268ed444.f51e76d509f94b93855bb8bc61521f93'
                        ])->post('http://46.102.156.214:3001/api/v1/messages', [
                            'recipient_type' => 'individual',
                            'to' => $whatsapp,
                            'type' => 'text',
                            'text' => [
                                'body' => $message
                            ]
                        ]);

                        Notification::make()
                            ->title($response->status() === 200 ?
                                'Pengingat telah terkirim' :
                                'Gagal mengirim pengingat')
                            ->status($response->status() === 200 ? 'success' : 'danger')
                            ->send();

                        $this->dispatch('spin-stop');
                    })
                    ->extraAttributes([
                        'x-data' => '{ spinning: false }',
                        'x-on:spin-start' => 'spinning = true',
                        'x-on:spin-stop' => 'spinning = false',
                        'x-bind:class' => "{ 'animate-spin': spinning }"
                    ])
            ]);
    }

    public function print()
    {
        try {
            $data = $this->getData();

            if ($data->isEmpty()) {
                Notification::make()
                    ->title('Tidak ada data keterlambatan')
                    ->warning()
                    ->send();
                return;
            }

            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'landscape');

            $html = view('pdf.keterlambatan-30-hari', [
                'data' => $data,
                'today' => Carbon::today()
            ])->render();

            $dompdf->loadHtml($html);
            $dompdf->render();

            return response()->streamDownload(
                fn () => print($dompdf->output()),
                $this->generatePdfFilename(),
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
        return 'laporan_keterlambatan_30_hari_' . date('Y-m-d_H-i-s') . '.pdf';
    }
}