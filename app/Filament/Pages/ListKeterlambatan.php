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
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Notifications\Notification;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ListKeterlambatan extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Pinjaman';
    protected static string $view = 'filament.pages.list-keterlambatan';

    public function getData()
    {
        $today = Carbon::today();

        return Pinjaman::query()
            ->with(['profile', 'biayaBungaPinjaman', 'denda'])
            ->where('status_pinjaman', 'approved')
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->get();
    }

    public function table(Table $table): Table
    {
        $today = Carbon::today();

        return $table
            ->query(
                Pinjaman::query()
                    ->with(['profile', 'biayaBungaPinjaman', 'denda'])
                    ->where('status_pinjaman', 'approved')
                    ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                        $q->whereMonth('tanggal_pembayaran', $today->month)
                          ->whereYear('tanggal_pembayaran', $today->year);
                    })
            )
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('profile.first_name')
                    ->label('Nama')
                    ->formatStateUsing(fn ($record) =>
                        $record->profile->first_name . ' ' . $record->profile->last_name
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_pinjaman')
                    ->label('No Pinjaman')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jumlah_pinjaman')
                    ->label('Angsuran Pokok')
                    ->money('IDR')
                    ->formatStateUsing(function ($record) {
                        $angsuranPokok = $record->jumlah_pinjaman / $record->jangka_waktu;
                        // return $record->jumlah_pinjaman / $record->jangka_waktu;
                        return $angsuranPokok;
                    }),

                TextColumn::make('jumlah_pinjaman')
                    ->label('Total Bayar')
                    ->formatStateUsing(function ($record) use ($today) {
                        try {
                            // Hitung angsuran pokok
                            $angsuranPokok = $record->jumlah_pinjaman / $record->jangka_waktu;

                            // Hitung denda
                            $tanggalJatuhTempo = Carbon::create(
                                $today->year,
                                $today->month,
                                Carbon::parse($record->tanggal_jatuh_tempo)->day
                            )->startOfDay();

                            $hariTerlambat = 0;
                            if ($today->gt($tanggalJatuhTempo)) {
                                $hariTerlambat = abs($tanggalJatuhTempo->diffInDays($today));
                            }

                            $denda = ($record->denda->rate_denda/100 * $angsuranPokok / 30) * $hariTerlambat;

                            // Total bayar
                            $totalBayar = $angsuranPokok + $denda;

                            // Format angka dengan prefix Rp, 2 desimal, dan pemisah ribuan
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
                        Log::info('Calculating hari terlambat for record:', [
                            'id_pinjaman' => $record->id_pinjaman,
                            'tanggal_jatuh_tempo' => $record->tanggal_jatuh_tempo
                        ]);

                        $tanggalJatuhTempo = Carbon::create(
                            $today->year,
                            $today->month,
                            Carbon::parse($record->tanggal_jatuh_tempo)->day
                        )->startOfDay();

                        $hariTerlambat = 0;
                        if ($today->gt($tanggalJatuhTempo)) {
                            $hariTerlambat = abs($tanggalJatuhTempo->diffInDays($today));
                        }

                        Log::info('Hari terlambat calculated:', [
                            'hari_terlambat' => $hariTerlambat
                        ]);

                        return $hariTerlambat . ' hari';
                    })
                    ->sortable(),

                TextColumn::make('profile.whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(function ($record) {
                        try {
                            $nama = $record->profile->first_name . ' ' . $record->profile->last_name;
                            $pesan = urlencode("Halo {$nama}, ini adalah pengingat untuk pembayaran angsuran pinjaman Anda yang sudah jatuh tempo. Mohon segera melakukan pembayaran. Terima kasih.");
                            $whatsapp = $record->profile->whatsapp;

                            // Hapus karakter selain angka
                            $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

                            // Tambahkan 62 jika dimulai dengan 0
                            if (substr($whatsapp, 0, 1) === '0') {
                                $whatsapp = '62' . substr($whatsapp, 1);
                            }

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
            $data = $this->getData();

            if ($data->isEmpty()) {
                Notification::make()
                    ->title('Tidak ada data keterlambatan')
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
            $html = view('pdf.keterlambatan', [
                'data' => $data,
                'today' => Carbon::today()
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
        return 'laporan_keterlambatan_' . date('Y-m-d_H-i-s') . '.pdf';
    }
}
