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
            // Filter pinjaman yang belum dibayar bulan ini
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->where(function ($query) use ($today) {
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    // Cek keterlambatan untuk transaksi yang sudah jatuh tempo
                    // dan berada di bulan yang berbeda
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 0', [$today])
                      ->whereRaw('DATE_FORMAT(tanggal_jatuh_tempo, "%Y-%m") < ?',
                          [$today->format('Y-m')]);
                })
                ->orWhere(function ($q) use ($today) {
                    // Untuk pinjaman yang belum pernah bayar
                    // Hanya tampilkan jika sudah melewati bulan pertama
                    $q->whereDoesntHave('transaksiPinjaman')
                      ->whereRaw('DATE_FORMAT(tanggal_pinjaman, "%Y-%m") < ?',
                          [$today->format('Y-m')])
                      ->whereRaw('DATEDIFF(?, DATE_ADD(tanggal_pinjaman, INTERVAL 1 MONTH)) > 0',
                          [$today]);
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

        // Jika masih dalam bulan yang sama dengan tanggal pinjaman, return 0
        if ($today->format('Y-m') === Carbon::parse($record->tanggal_pinjaman)->format('Y-m')) {
            return 0;
        }

        // Hitung keterlambatan hanya jika sudah melewati tanggal jatuh tempo
        // dan berada di bulan yang berbeda
        if ($today->gt($tanggalJatuhTempo) &&
            $today->format('Y-m') !== $tanggalJatuhTempo->format('Y-m')) {
            return $today->diffInDays($tanggalJatuhTempo);
        }

        return 0;
    }

    private function calculateDenda($record, $angsuranPokok, $hariTerlambat)
    {
        // Hitung angsuran total per bulan (pokok + bunga)
        $angsuranBunga = $this->calculateBungaPerBulan($record);
        $angsuranTotal = $angsuranPokok + $angsuranBunga;

        // Hitung denda per hari (5% x Angsuran Total / 30)
        $dendaPerHari = (0.05 * $angsuranTotal) / 30;

        // Total denda = denda per hari x jumlah hari terlambat
        return $dendaPerHari * $hariTerlambat;
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

    private function calculateJumlahBulanTerlambat($record, $today)
    {
        $lastTransaction = $record->transaksiPinjaman()
            ->orderBy('angsuran_ke', 'desc')
            ->first();

        if ($lastTransaction) {
            $tanggalJatuhTempo = Carbon::parse($lastTransaction->tanggal_pembayaran)
                ->addMonth()
                ->startOfDay();
        } else {
            $tanggalJatuhTempo = Carbon::parse($record->tanggal_pinjaman)
                ->addMonth()
                ->startOfDay();
        }

        return ceil($today->diffInDays($tanggalJatuhTempo) / 30);
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
                            // 1. Hitung jumlah bulan terlambat (pembulatan ke atas)
                            $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
                            $jumlahBulanTerlambat = ceil($hariTerlambat / 30);

                            // 2. Hitung angsuran pokok dan bunga per bulan
                            $angsuranPokok = abs($this->calculateAngsuranPokok($record));
                            $bungaPerBulan = abs($this->calculateBungaPerBulan($record));

                            // 3. Hitung total pokok untuk periode keterlambatan
                            $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;

                            // 4. Hitung total bunga untuk periode keterlambatan
                            $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;

                            // 5. Hitung total denda
                            $angsuranTotal = $angsuranPokok + $bungaPerBulan;
                            $dendaPerHari = (0.05 * $angsuranTotal) / 30;
                            $totalDenda = $dendaPerHari * $hariTerlambat;

                            // 6. Total keseluruhan (pokok + bunga + denda)
                            $totalBayar = $totalPokok + $totalBunga + $totalDenda;

                            // Debug log untuk memverifikasi perhitungan
                            Log::info("Perhitungan Total Bayar:", [
                                'hari_terlambat' => $hariTerlambat,
                                'jumlah_bulan' => $jumlahBulanTerlambat,
                                'angsuran_pokok' => $angsuranPokok,
                                'bunga_per_bulan' => $bungaPerBulan,
                                'total_pokok' => $totalPokok,
                                'total_bunga' => $totalBunga,
                                'denda_per_hari' => $dendaPerHari,
                                'total_denda' => $totalDenda,
                                'total_bayar' => $totalBayar
                            ]);

                            return 'Rp.' . number_format($totalBayar, 2, ',', '.');
                        } catch (\Exception $e) {
                            Log::error('Error calculating total bayar: ' . $e->getMessage());
                            return 'Rp.0,00';
                        }
                    })
                    ->sortable(),

                TextColumn::make('tanggal_pinjaman')
                    ->label('Tanggal Pinjaman')
                    ->date()
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

                        try {
                            $nama = trim("{$record->profile->first_name} {$record->profile->last_name}");

                            // Hitung komponen-komponen
                            $hariTerlambat = abs($this->calculateHariTerlambat($record, Carbon::today()));
                            $jumlahBulanTerlambat = ceil($hariTerlambat / 30);

                            $angsuranPokok = abs($this->calculateAngsuranPokok($record));
                            $bungaPerBulan = abs($this->calculateBungaPerBulan($record));

                            // Total pokok untuk periode keterlambatan
                            $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;

                            // Total bunga untuk periode keterlambatan
                            $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;

                            // Hitung denda
                            $angsuranTotal = $angsuranPokok + $bungaPerBulan;
                            $dendaPerHari = (0.05 * $angsuranTotal) / 30;
                            $totalDenda = $dendaPerHari * $hariTerlambat;

                            // Total keseluruhan
                            $totalBayar = $totalPokok + $totalBunga + $totalDenda;

                            $message = "Halo *{$nama}*,\n\n"
                                . "Ini adalah pengingat untuk pembayaran angsuran pinjaman Anda yang belum dibayar:\n\n"
                                . "No Pinjaman: *{$record->no_pinjaman}*\n"
                                . "Angsuran Pokok Belum Dibayar: *Rp." . number_format($totalPokok, 2, ',', '.') . "*\n"
                                . "Bunga Belum Dibayar: *Rp." . number_format($totalBunga, 2, ',', '.') . "*\n"
                                . "Denda Belum Dibayar: *Rp." . number_format($totalDenda, 2, ',', '.') . "*\n"
                                . "Total Belum Dibayar: *Rp." . number_format($totalBayar, 2, ',', '.') . "*\n"
                                . "Keterlambatan: *" . abs($hariTerlambat) . " hari*\n\n"
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

                            if ($response->status() === 200) {
                                // Kirim data ke webhook N8N
                                $this->sendToWebhook($whatsapp, $message, $record);
                            }

                            Notification::make()
                                ->title($response->status() === 200 ?
                                    'Pengingat telah terkirim' :
                                    'Gagal mengirim pengingat')
                                ->status($response->status() === 200 ? 'success' : 'danger')
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Error sending reminder: ' . $e->getMessage());
                            Notification::make()
                                ->title('Gagal mengirim pengingat')
                                ->danger()
                                ->send();
                        }

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

    private function sendToWebhook($whatsapp, $message, $record)
    {
        try {
            $webhookUrl = env('WEBHOOK_WA_N8N');
            
            if (empty($webhookUrl)) {
                Log::warning('WEBHOOK_WA_N8N tidak dikonfigurasi di .env');
                return;
            }

            $payload = [
                'whatsapp' => $whatsapp,
                'message' => $message,
                'pinjaman_id' => $record->id,
                'no_pinjaman' => $record->no_pinjaman,
                'source' => 'list_keterlambatan_bulan_ini',
                'timestamp' => now()->toISOString()
            ];

            $response = Http::timeout(30)->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Data berhasil dikirim ke webhook N8N dari List Keterlambatan Bulan Ini', [
                    'pinjaman_id' => $record->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Gagal mengirim data ke webhook N8N dari List Keterlambatan Bulan Ini', [
                    'pinjaman_id' => $record->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error mengirim data ke webhook N8N dari List Keterlambatan Bulan Ini: ' . $e->getMessage(), [
                'pinjaman_id' => $record->id,
                'webhook_url' => $webhookUrl ?? 'tidak tersedia'
            ]);
        }
    }
}
