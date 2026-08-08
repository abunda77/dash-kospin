<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
        @php
            $activeSetoran = $this->getActiveSetoran();
        @endphp

        @if ($activeSetoran)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    @if ($activeSetoran->metode_pembayaran === \App\Enums\MetodePembayaranSetoran::TransferRekening)
                        <div class="w-full max-w-md">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400">
                                <x-heroicon-o-building-library class="h-9 w-9" />
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Transfer ke Rekening Tujuan</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Transfer tepat sesuai total pembayaran berikut.</p>

                            <div class="mt-5 divide-y divide-gray-200 overflow-hidden rounded-xl border border-gray-200 text-left dark:divide-gray-700 dark:border-gray-700">
                                <div class="flex justify-between gap-4 px-4 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Bank</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ config('setoran.rekening_transfer.bank') }}</span>
                                </div>
                                <div class="flex justify-between gap-4 px-4 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No. Rekening</span>
                                    <span class="font-mono text-lg font-bold tracking-wide text-primary-600 dark:text-primary-400">{{ config('setoran.rekening_transfer.nomor_rekening') }}</span>
                                </div>
                                <div class="flex justify-between gap-4 px-4 py-3">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Atas Nama</span>
                                    <span class="text-right font-bold text-gray-900 dark:text-white">{{ config('setoran.rekening_transfer.atas_nama') }}</span>
                                </div>
                            </div>

                            <div class="mt-5 rounded-xl bg-success-50 px-4 py-4 dark:bg-success-950/30">
                                <p class="text-xs font-semibold uppercase tracking-wide text-success-700 dark:text-success-400">Total Transfer</p>
                                <p class="mt-1 text-2xl font-black text-success-700 dark:text-success-400">Rp {{ number_format($activeSetoran->jumlah_bayar, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @else
                        <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Pindai QRIS untuk Bayar</h3>

                        @if ($activeSetoran->qris_image_path)
                            <div class="inline-block rounded-lg bg-white p-4 shadow-inner">
                                <img src="{{ Storage::disk('public')->url($activeSetoran->qris_image_path) }}" alt="QRIS Setoran" class="mx-auto h-64 w-64 md:h-80 md:w-80" />
                            </div>
                        @else
                            <div class="flex h-64 w-64 items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-700 md:h-80 md:w-80">
                                QRIS Image tidak tersedia
                            </div>
                        @endif

                        <p class="mt-4 text-sm font-semibold text-gray-500 dark:text-gray-400">Scan QRIS menggunakan aplikasi pembayaran (GoPay, OVO, DANA, LinkAja, atau Mobile Banking)</p>
                    @endif

                    <div class="mt-4 space-y-2">
                        <div class="inline-block rounded bg-danger-50 px-3 py-1 text-xs font-bold text-danger-600 dark:bg-danger-950/30 dark:text-danger-400">
                            Kedaluwarsa pada: {{ $activeSetoran->kedaluwarsa_at->format('d M Y, H:i') }}
                        </div>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full 
                                @if (in_array($activeSetoran->status->value, ['menunggu_pembayaran', 'perlu_revisi']))
                                    bg-warning-50 text-warning-700 dark:bg-warning-950/30 dark:text-warning-400
                                @elseif (in_array($activeSetoran->status->value, ['menunggu_verifikasi', 'sedang_diperiksa']))
                                    bg-info-50 text-info-700 dark:bg-info-950/30 dark:text-info-400
                                @else
                                    bg-success-50 text-success-700 dark:bg-success-950/30 dark:text-success-400
                                @endif">
                                Status: {{ str_replace('_', ' ', strtoupper($activeSetoran->status->value)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Detail Informasi Setoran</h3>
                        
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Metode Pembayaran</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activeSetoran->metode_pembayaran->label() }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nomor Setoran</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">#{{ $activeSetoran->nomor_setoran }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Rekening</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activeSetoran->tabungan->no_tabungan }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Jenis Simpanan</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $activeSetoran->jenis_simpanan }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nominal Setoran</span>
                                <span class="text-sm text-gray-900 dark:text-white font-semibold">Rp {{ number_format($activeSetoran->jumlah, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Kode Unik</span>
                                <span class="text-sm text-gray-900 dark:text-white font-semibold">{{ $activeSetoran->kode_unik }}</span>
                            </div>
                            <div class="flex justify-between py-2.5 border-t border-gray-200 dark:border-gray-700 pt-3">
                                <span class="text-base font-bold text-gray-900 dark:text-white">Total Transfer (Wajib Sama)</span>
                                <span class="text-base font-bold text-success-600 dark:text-success-400">Rp {{ number_format($activeSetoran->jumlah_bayar, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if ($activeSetoran->status->value === 'perlu_revisi' && $activeSetoran->catatan_verifikasi)
                            <div class="mt-4 p-4 bg-danger-50 dark:bg-danger-950/20 text-danger-700 dark:text-danger-400 rounded-lg border border-danger-100 dark:border-danger-900 text-sm">
                                <span class="font-bold">Catatan Verifikasi:</span> {{ $activeSetoran->catatan_verifikasi }}
                            </div>
                        @endif
                    </div>

                    @if (in_array($activeSetoran->status->value, ['menunggu_pembayaran', 'perlu_revisi']))
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Saya Sudah Membayar</h3>
                            
                            <form wire:submit.prevent="claimPayment({{ $activeSetoran->id }})" class="space-y-4">
                                {{ $this->claimForm }}

                                <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
                                    @if ($activeSetoran->status === \App\Enums\StatusSetoran::MENUNGGU_PEMBAYARAN)
                                        <x-filament::button
                                            type="button"
                                            color="danger"
                                            size="lg"
                                            wire:click="cancelSetoran({{ $activeSetoran->id }})"
                                            wire:confirm="Batalkan setoran ini? Instruksi pembayaran tidak dapat digunakan kembali setelah setoran dibatalkan."
                                            wire:loading.attr="disabled"
                                            wire:target="cancelSetoran"
                                        >
                                            Batalkan Setoran
                                        </x-filament::button>
                                    @endif

                                    <x-filament::button type="submit" color="success" size="lg">
                                        Kirim Bukti Pembayaran
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-gray-55 dark:bg-gray-800/40 p-6 rounded-xl border border-gray-150 dark:border-gray-750 text-center">
                            <x-heroicon-o-clock class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Setoran Berhasil Diajukan</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Status saat ini sedang dalam proses verifikasi atau disetujui. Mohon menunggu informasi lebih lanjut.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Isi Formulir Setoran Baru</h3>

                <form wire:submit.prevent="createSetoran" class="space-y-4">
                    {{ $this->generateForm }}

                    <div class="flex justify-end mt-4">
                        <x-filament::button type="submit" color="primary" size="lg">
                            Lanjutkan Pembayaran
                        </x-filament::button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Setoran</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            <th class="px-4 py-3">Tanggal Buat</th>
                            <th class="px-4 py-3">Nomor</th>
                            <th class="px-4 py-3">Rekening Tujuan</th>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3 text-right">Jumlah Setor</th>
                            <th class="px-4 py-3 text-right">Total Bayar</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getHistorySetoran() as $item)
                            <tr class="text-sm text-gray-800 transition-colors odd:bg-white even:bg-slate-50/80 hover:bg-emerald-50/70 dark:text-gray-100 dark:odd:bg-gray-800 dark:even:bg-slate-800/70 dark:hover:bg-emerald-950/20">
                                <td class="px-4 py-3">{{ $item->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">#{{ $item->nomor_setoran }}</td>
                                <td class="px-4 py-3">{{ $item->tabungan->no_tabungan ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->metode_pembayaran->label() }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusColor = match ($item->status->value) {
                                            'selesai', 'disetujui' => 'success',
                                            'menunggu_pembayaran' => 'warning',
                                            'menunggu_verifikasi' => 'info',
                                            'sedang_diperiksa' => 'primary',
                                            'perlu_revisi' => 'warning',
                                            'ditolak' => 'danger',
                                            'dibatalkan' => 'gray',
                                            default => 'gray',
                                        };
                                    @endphp

                                    <x-filament::badge :color="$statusColor">
                                        {{ str_replace('_', ' ', strtoupper($item->status->value)) }}
                                    </x-filament::badge>
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-xs text-gray-700 dark:text-gray-200">
                                    @if ($item->status->value === 'ditolak' && $item->alasan_penolakan)
                                        <span class="font-medium text-red-700 dark:text-red-300">Ditolak: {{ $item->alasan_penolakan }}</span>
                                    @elseif ($item->status->value === 'perlu_revisi' && $item->catatan_verifikasi)
                                        <span class="font-medium text-amber-700 dark:text-amber-300">Revisi: {{ $item->catatan_verifikasi }}</span>
                                    @else
                                        {{ $item->catatan_pengguna ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada riwayat transaksi setoran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
