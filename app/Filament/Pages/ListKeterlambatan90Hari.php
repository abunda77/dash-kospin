<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Pinjaman;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action;
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
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Colors\Color;

class ListKeterlambatan90Hari extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'List Telat > 90 Hari';
    protected static ?string $title = 'List Telat Lebih Dari 90 Hari';

    protected static string $view = 'filament.pages.list-keterlambatan-90-hari';

    public static function getNavigationGroup(): ?string
    {
        return 'Pinjaman';
    }

    public static function getNavigationBadge(): ?string
    {
        $today = Carbon::today();

        return Pinjaman::query()
            ->where('status_pinjaman', 'approved')
            ->where(function ($query) use ($today) {
                // Cek pinjaman yang memiliki transaksi dengan keterlambatan > 90 hari
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 90', [$today]);
                })
                // ATAU pinjaman yang belum pernah bayar sama sekali dengan keterlambatan > 90 hari
                ->orWhereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_pinjaman) > 90', [$today]);
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
                    // Cek keterlambatan untuk transaksi dengan keterlambatan > 90 hari
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 90', [$today])
                      ->whereRaw('DATE_FORMAT(tanggal_jatuh_tempo, "%Y-%m") < ?',
                          [$today->format('Y-m')]);
                })
                ->orWhere(function ($q) use ($today) {
                    // Untuk pinjaman yang belum pernah bayar dengan keterlambatan > 90 hari
                    $q->whereDoesntHave('transaksiPinjaman')
                      ->whereRaw('DATE_FORMAT(tanggal_pinjaman, "%Y-%m") < ?',
                          [$today->format('Y-m')])
                      ->whereRaw('DATEDIFF(?, DATE_ADD(tanggal_pinjaman, INTERVAL 1 MONTH)) > 90',
                          [$today]);
                });
            });
    }

    public function getData()
    {
        return $this->getBaseQuery()->get();
    }

    public function getStatsData(): array
    {
        $data = $this->getData();
        $today = Carbon::today();
        
        $totalPinjaman = $data->count();
        $totalNominalPinjaman = abs($data->sum('jumlah_pinjaman'));
        $totalAngsuranPokok = abs($data->sum(function ($record) {
            return $this->calculateAngsuranPokok($record);
        }));
        
        $totalDenda = abs($data->sum(function ($record) use ($today) {
            $angsuranPokok = $this->calculateAngsuranPokok($record);
            $hariTerlambat = $this->calculateHariTerlambat($record, $today);
            return $this->calculateDenda($record, $angsuranPokok, $hariTerlambat);
        }));
        
        $totalTunggakan = abs($data->sum(function ($record) use ($today) {
            $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
            $jumlahBulanTerlambat = ceil($hariTerlambat / 30);
            
            $angsuranPokok = abs($this->calculateAngsuranPokok($record));
            $bungaPerBulan = abs($this->calculateBungaPerBulan($record));
            
            $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;
            $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;
            
            $angsuranTotal = $angsuranPokok + $bungaPerBulan;
            $dendaPerHari = (0.05 * $angsuranTotal) / 30;
            $totalDenda = $dendaPerHari * $hariTerlambat;
            
            return $totalPokok + $totalBunga + $totalDenda;
        }));
        
        $rataRataHariTerlambat = abs($data->avg(function ($record) use ($today) {
            return abs($this->calculateHariTerlambat($record, $today));
        }));

        $statsData = [
            'total_pinjaman' => $totalPinjaman,
            'total_nominal_pinjaman' => $totalNominalPinjaman,
            'total_angsuran_pokok' => $totalAngsuranPokok,
            'total_denda' => $totalDenda,
            'total_tunggakan' => $totalTunggakan,
            'rata_rata_hari_terlambat' => $rataRataHariTerlambat,
        ];

        $statsData['rasio_pinjaman_bermasalah'] = abs($this->calculateRasioPinjamanBermasalah($statsData['total_nominal_pinjaman']));

        return $statsData;
    }

    public function getStatsWidgets(): array
    {
        $stats = $this->getStatsData();
        
        return [
            Stat::make('Rasio Pinjaman Bermasalah', number_format($stats['rasio_pinjaman_bermasalah'], 2, ',', '.') . '%')
                ->description('Pinjaman Bermasalah / Total Pinjaman Dicairkan')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color(Color::Red),
                
            Stat::make('Total Akun Bermasalah', number_format($stats['total_pinjaman']))
                ->description('Pinjaman dengan keterlambatan > 90 hari')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(Color::Red),
                
            Stat::make('Total Nominal Pinjaman Bermasalah', 'Rp ' . number_format($stats['total_nominal_pinjaman'], 0, ',', '.'))
                ->description('Nilai total pinjaman bermasalah')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Orange),
                
            Stat::make('Total Tunggakan', 'Rp ' . number_format($stats['total_tunggakan'], 0, ',', '.'))
                ->description('Total pokok + bunga + denda')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color(Color::Red),
                
            Stat::make('Total Denda', 'Rp ' . number_format($stats['total_denda'], 0, ',', '.'))
                ->description('Akumulasi denda keterlambatan')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color(Color::Rose),
                
            Stat::make('Rata-rata Keterlambatan', number_format($stats['rata_rata_hari_terlambat'], 0) . ' hari')
                ->description('Rata-rata hari keterlambatan')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Amber),
                
            Stat::make('Total Angsuran Pokok', 'Rp ' . number_format($stats['total_angsuran_pokok'], 0, ',', '.'))
                ->description('Total angsuran pokok terhutang')
                ->descriptionIcon('heroicon-m-calculator')
                ->color(Color::Gray),
        ];
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

    private function calculateRasioPinjamanBermasalah($totalPinjamanBermasalah)
    {
        $totalPinjamanDicairkan = Pinjaman::where('status_pinjaman', 'approved')->sum('jumlah_pinjaman');

        if ($totalPinjamanDicairkan > 0) {
            return ($totalPinjamanBermasalah / $totalPinjamanDicairkan) * 100;
        }

        return 0;
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
                    ->label('Nominal Pinjaman')
                    ->money('IDR')
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
                    ->label('Total Tunggakan')
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
                            $totalTunggakan = $totalPokok + $totalBunga + $totalDenda;

                            return 'Rp.' . number_format($totalTunggakan, 2, ',', '.');
                        } catch (\Exception $e) {
                            Log::error('Error calculating total tunggakan: ' . $e->getMessage());
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
                    ->badge()
                    ->color(function ($record) use ($today) {
                        $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
                        if ($hariTerlambat >= 180) return Color::Red;
                        if ($hariTerlambat >= 120) return Color::Orange;
                        return Color::Yellow;
                    })
                    ->sortable(),

                TextColumn::make('profile.whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(function ($record) {
                        try {
                            $nama = trim("{$record->profile->first_name} {$record->profile->last_name}");
                            $pesan = urlencode("Halo {$nama}, ini adalah pemberitahuan penting mengenai tunggakan pinjaman Anda yang sudah melewati 90 hari. Mohon segera melakukan pembayaran untuk menghindari tindakan lebih lanjut. Terima kasih.");
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
                    ->sortable(),            ])
            ->actions([
                TableAction::make('send_urgent_reminder')
                    ->label('Kirim Peringatan Urgent')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
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
                            $totalTunggakan = $totalPokok + $totalBunga + $totalDenda;

                            $message = "🚨 *PERINGATAN URGENT* 🚨\n\n"
                                . "Kepada Yth. *{$nama}*,\n\n"
                                . "Pinjaman Anda telah mengalami keterlambatan *LEBIH DARI 90 HARI*:\n\n"
                                . "📋 No Pinjaman: *{$record->no_pinjaman}*\n"
                                . "💰 Total Tunggakan: *Rp." . number_format($totalTunggakan, 2, ',', '.') . "*\n"
                                . "⏰ Keterlambatan: *" . abs($hariTerlambat) . " hari*\n\n"
                                . "Rincian:\n"
                                . "• Pokok: Rp." . number_format($totalPokok, 2, ',', '.') . "\n"
                                . "• Bunga: Rp." . number_format($totalBunga, 2, ',', '.') . "\n"
                                . "• Denda: Rp." . number_format($totalDenda, 2, ',', '.') . "\n\n"
                                . "⚠️ *PERHATIAN:* Keterlambatan ini dapat berdampak pada:\n"
                                . "- Peningkatan denda harian\n"
                                . "- Tindakan penagihan lebih lanjut\n"
                                . "- Pencatatan di sistem kredit\n\n"
                                . "🔔 Mohon segera hubungi kantor untuk penyelesaian.\n\n"
                                . "Koperasi SinaraArtha\n"
                                . "📞 Telp / WA: [087778715788]";

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

                            // Kirim data ke webhook N8N
                            $this->sendToWebhook($whatsapp, $message, $record, $response->status());

                            Notification::make()
                                ->title($response->status() === 200 ? 
                                    'Peringatan urgent berhasil dikirim' : 
                                    'Gagal mengirim peringatan urgent')
                                ->color($response->status() === 200 ? 'success' : 'danger')
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Error sending urgent reminder: ' . $e->getMessage());
                            Notification::make()
                                ->title('Terjadi kesalahan')
                                ->body($e->getMessage())
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->dispatch('$refresh');
                    Notification::make()
                        ->title('Data berhasil direfresh')
                        ->success()
                        ->send();
                }),
                
            Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->action(fn () => $this->print()),
        ];
    }

    public function print()
    {
        try {
            $data = $this->getData();

            if ($data->isEmpty()) {
                Notification::make()
                    ->title('Tidak ada data keterlambatan > 90 hari')
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

            $html = view('pdf.keterlambatan-90-hari', [
                'data' => $data,
                'today' => Carbon::today(),
                'stats' => $this->getStatsData()
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
        return 'laporan_keterlambatan_90_hari_' . date('Y-m-d_H-i-s') . '.pdf';
    }

    private function sendToWebhook($whatsapp, $message, $record, $whatsappStatus = null)
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
                'source' => 'list_keterlambatan_90_hari',
                'whatsapp_status_code' => $whatsappStatus,
                'whatsapp_sent_successfully' => $whatsappStatus === 200,
                'timestamp' => now()->toISOString(),
                'urgency_level' => 'high',
                'delay_days' => abs($this->calculateHariTerlambat($record, Carbon::today()))
            ];

            $response = Http::timeout(30)->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Data berhasil dikirim ke webhook N8N dari List Keterlambatan 90 Hari', [
                    'pinjaman_id' => $record->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Gagal mengirim data ke webhook N8N dari List Keterlambatan 90 Hari', [
                    'pinjaman_id' => $record->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error mengirim data ke webhook N8N dari List Keterlambatan 90 Hari: ' . $e->getMessage(), [
                'pinjaman_id' => $record->id,
                'webhook_url' => $webhookUrl ?? 'tidak tersedia'
            ]);
        }
    }
}
