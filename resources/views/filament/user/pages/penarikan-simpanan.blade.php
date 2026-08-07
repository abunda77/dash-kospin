<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
        @php
            $activePenarikan = $this->getActivePenarikan();
        @endphp

        @if ($activePenarikan)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center text-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Status Permohonan Penarikan</h3>

                    <div class="w-full space-y-3">
                        <x-heroicon-o-banknotes class="w-16 h-16 text-success-500 mx-auto" />

                        <div class="text-xs text-gray-500 dark:text-gray-400 font-bold bg-gray-50 dark:bg-gray-950/30 px-3 py-2 rounded inline-block">
                            Diajukan pada: {{ $activePenarikan->dikirim_at?->format('d M Y, H:i') ?? $activePenarikan->created_at->format('d M Y, H:i') }}
                        </div>

                        <div>
                            <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full
                                @if ($activePenarikan->status->value === 'perlu_revisi')
                                    bg-danger-50 text-danger-700 dark:bg-danger-950/30 dark:text-danger-400
                                @elseif (in_array($activePenarikan->status->value, ['menunggu_verifikasi', 'sedang_diperiksa']))
                                    bg-info-50 text-info-700 dark:bg-info-950/30 dark:text-info-400
                                @else
                                    bg-success-50 text-success-700 dark:bg-success-950/30 dark:text-success-400
                                @endif">
                                Status: {{ str_replace('_', ' ', strtoupper($activePenarikan->status->value)) }}
                            </span>
                        </div>

                        @if ($activePenarikan->status->value === 'perlu_revisi' && $activePenarikan->catatan_verifikasi)
                            <div class="mt-2 p-4 bg-danger-50 dark:bg-danger-950/20 text-danger-700 dark:text-danger-400 rounded-lg border border-danger-100 dark:border-danger-900 text-sm text-left">
                                <span class="font-bold">Catatan Verifikasi:</span> {{ $activePenarikan->catatan_verifikasi }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Detail Informasi Penarikan</h3>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nomor Penarikan</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">#{{ $activePenarikan->nomor_penarikan }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Rekening</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activePenarikan->tabungan->no_tabungan }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Jenis Simpanan</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $activePenarikan->jenis_simpanan }}</span>
                            </div>
                            <div class="flex justify-between py-2.5 border-t border-gray-200 dark:border-gray-700 pt-3">
                                <span class="text-base font-bold text-gray-900 dark:text-white">Nominal Penarikan</span>
                                <span class="text-base font-bold text-danger-600 dark:text-danger-400">Rp {{ number_format($activePenarikan->jumlah, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Bank</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activePenarikan->bank }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nama Bank</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activePenarikan->nama_bank }}</span>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nama Nasabah</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $activePenarikan->nama_nasabah }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($activePenarikan->status->value === 'perlu_revisi')
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Kirim Revisi Penarikan</h3>

                            <form wire:submit.prevent="kirimRevisi({{ $activePenarikan->id }})" class="space-y-4">
                                {{ $this->revisiForm }}

                                <div class="flex justify-end mt-4">
                                    <x-filament::button type="submit" color="success" size="lg">
                                        Kirim Revisi
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-gray-55 dark:bg-gray-800/40 p-6 rounded-xl border border-gray-150 dark:border-gray-750 text-center">
                            <x-heroicon-o-clock class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Penarikan Berhasil Diajukan</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Status saat ini sedang dalam proses verifikasi atau disetujui. Mohon menunggu informasi lebih lanjut.</p>

                            @if ($activePenarikan->status === \App\Enums\StatusPenarikan::MENUNGGU_VERIFIKASI)
                                <div class="mt-4 flex justify-center">
                                    <x-filament::button
                                        type="button"
                                        color="danger"
                                        size="lg"
                                        wire:click="cancelPenarikan({{ $activePenarikan->id }})"
                                        wire:confirm="Batalkan pengajuan penarikan ini? Pengajuan yang dibatalkan tidak dapat diproses kembali."
                                        wire:loading.attr="disabled"
                                        wire:target="cancelPenarikan"
                                    >
                                        Batalkan Penarikan
                                    </x-filament::button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Isi Formulir Penarikan Simpanan Baru</h3>

                <form wire:submit.prevent="ajukanPenarikan" class="space-y-4">
                    {{ $this->createForm }}

                    <div class="flex justify-end mt-4">
                        <x-filament::button type="submit" color="primary" size="lg">
                            Ajukan Penarikan
                        </x-filament::button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mt-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Penarikan Simpanan</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            <th class="px-4 py-3">Tanggal Buat</th>
                            <th class="px-4 py-3">Nomor</th>
                            <th class="px-4 py-3">Rekening Sumber</th>
                            <th class="px-4 py-3 text-right">Jumlah Tarik</th>
                            <th class="px-4 py-3">Bank Tujuan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getHistoryPenarikan() as $item)
                            <tr class="text-sm text-gray-800 transition-colors odd:bg-white even:bg-slate-50/80 hover:bg-emerald-50/70 dark:text-gray-100 dark:odd:bg-gray-800 dark:even:bg-slate-800/70 dark:hover:bg-emerald-950/20">
                                <td class="px-4 py-3">{{ $item->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">#{{ $item->nomor_penarikan }}</td>
                                <td class="px-4 py-3">{{ $item->tabungan->no_tabungan ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $item->nama_bank }} a.n. {{ $item->nama_nasabah }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusColor = match ($item->status->value) {
                                            'selesai', 'disetujui' => 'success',
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
                                <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada riwayat transaksi penarikan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
