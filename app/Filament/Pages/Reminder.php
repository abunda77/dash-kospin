<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Pinjaman;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Notifications\Notification;

class Reminder extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Reminder';
    protected static ?string $title = 'Reminder';

    public static function getNavigationGroup(): ?string
    {
        Log::info('Getting navigation group');
        return 'Pinjaman';
    }

    protected static string $view = 'filament.pages.reminder';

    private function getBaseQuery()
    {
        $today = Carbon::today();
        $threeDaysFromNow = $today->copy()->addDays(3);

        return Pinjaman::query()
            ->with(['profile'])
            ->where('status_pinjaman', 'approved')
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->where(function ($query) use ($today, $threeDaysFromNow) {
                $query->whereRaw('DAY(tanggal_jatuh_tempo) >= ?', [$today->day])
                      ->whereRaw('DAY(tanggal_jatuh_tempo) <= ?', [$threeDaysFromNow->day]);
            });
    }

    private function calculateAngsuranPokok($record)
    {
        Log::info('Calculating angsuran pokok', ['pinjaman_id' => $record->id]);
        $result = $record->jumlah_pinjaman / $record->jangka_waktu;
        Log::info('Angsuran pokok calculated', ['result' => $result]);
        return $result;
    }

    private function formatWhatsAppNumber($whatsapp)
    {
        Log::info('Formatting WhatsApp number', ['original' => $whatsapp]);
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

        if (substr($whatsapp, 0, 1) === '0') {
            $whatsapp = '62' . substr($whatsapp, 1);
        }

        Log::info('WhatsApp number formatted', ['formatted' => $whatsapp]);
        return $whatsapp;
    }

    private function calculateSisaHari($record, $today)
    {
        // Mengambil tanggal dari tanggal_jatuh_tempo (misal: tanggal 16)
        $tanggalPembayaran = Carbon::parse($record->tanggal_jatuh_tempo)->day;

        // Membuat tanggal jatuh tempo untuk bulan ini
        // Contoh: jika hari ini 14 Des 2024 dan tanggal pembayaran 16
        // maka jatuh tempo = 16 Des 2024
        $jatuhTempoBulanIni = Carbon::create(
            $today->year,
            $today->month,
            $tanggalPembayaran
        )->startOfDay();

        // Jika hari ini sudah melewati tanggal pembayaran
        // maka hitung ke bulan depan
        // Contoh: jika hari ini 17 Des 2024, maka jatuh tempo = 16 Jan 2025
        if ($today->day > $tanggalPembayaran) {
            $jatuhTempoBulanIni->addMonth();
        }

        // Menghitung selisih hari:
        // - Jika hari ini 14 Des dan jatuh tempo 16 Des = 2 hari
        // - Jika hari ini 15 Des dan jatuh tempo 16 Des = 1 hari
        // - Jika hari ini 16 Des dan jatuh tempo 16 Des = 0 hari
        // - Jika hari ini 17 Des maka hitung ke 16 Jan = 30 hari
        return $today->diffInDays($jatuhTempoBulanIni, false);
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
                        try {
                            $angsuranPokok = $this->calculateAngsuranPokok($record);
                            return 'Rp.' . number_format($angsuranPokok, 2, ',', '.');
                        } catch (\Exception $e) {
                            Log::error('Error calculating angsuran pokok: ' . $e->getMessage());
                            return 'Rp.0,00';
                        }
                    })
                    ->sortable(),

                // TextColumn::make('tanggal_jatuh_tempo')
                //     ->label('Tanggal Jatuh Tempo')
                //     ->formatStateUsing(function ($record) {
                //         $tanggal = Carbon::parse($record->tanggal_jatuh_tempo)->day;
                //         $today = Carbon::today();
                //         return Carbon::create($today->year, $today->month, $tanggal)->format('d F Y');
                //     })
                //     ->sortable(),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Sisa Hari')
                    ->formatStateUsing(function ($record) use ($today) {
                        try {
                            $sisaHari = $this->calculateSisaHari($record, $today);
                            $text = $sisaHari . ' hari';
                            return $text;
                        } catch (\Exception $e) {
                            Log::error('Error calculating sisa hari: ' . $e->getMessage(), [
                                'record_id' => $record->id,
                                'exception' => $e
                            ]);
                            return 'Error';
                        }
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0 hari' => 'danger',
                        '1 hari' => 'warning',
                        '2 hari' => 'info',
                        '3 hari' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('profile.whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(function ($record) {
                        try {
                            $nama = trim("{$record->profile->first_name} {$record->profile->last_name}");
                            $pesan = urlencode("Halo {$nama}, ini adalah pengingat bahwa pembayaran angsuran pinjaman Anda akan jatuh tempo dalam beberapa hari. Mohon siapkan pembayaran Anda. Terima kasih.");
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
            ]);
    }

    public function print()
    {
        try {
            $data = $this->getBaseQuery()->get();

            if ($data->isEmpty()) {
                Notification::make()
                    ->title('Tidak ada data reminder')
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
            $dompdf->setPaper('A4', 'portrait');

            $html = view('pdf.reminder', [
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
        return 'laporan_reminder_' . date('Y-m-d_H-i-s') . '.pdf';
    }
}
