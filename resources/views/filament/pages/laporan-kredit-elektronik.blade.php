<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Laporan</h3>
            {{ $this->form }}
            
            <div class="mt-4 flex gap-4">
                <x-filament::button wire:click="cetakPDF" color="primary">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                    Cetak PDF
                </x-filament::button>
            </div>
        </div>

        <!-- Widgets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $query = $this->getBaseQuery();
                $totalKredit = $query->count();
                $totalNilai = $query->sum('nilai_hutang');
                $kreditAktif = $query->clone()->where('status_kredit', 'aktif')->count();
                $kreditLunas = $query->clone()->where('status_kredit', 'lunas')->count();
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-300">Total Kredit</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalKredit) }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">Jumlah total kredit elektronik</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-300">Total Nilai</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">Total nilai kredit</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-300">Kredit Aktif</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($kreditAktif) }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">Kredit yang masih aktif</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-300">Kredit Lunas</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($kreditLunas) }}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">Kredit yang sudah lunas</div>
            </div>
        </div>

        <!-- Data Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Data Kredit Elektronik</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Anggota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Harga Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uang Muka</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nilai Hutang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getBaseQuery()->get() as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $item->kode_barang }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $item->pinjaman->profile->nama_lengkap ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->nama_barang }}</div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500">{{ $item->jenis_barang }} - {{ $item->merk }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ format_rupiah($item->harga_barang) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ format_rupiah($item->uang_muka) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ format_rupiah($item->nilai_hutang) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($item->status_kredit === 'aktif') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                        @elseif($item->status_kredit === 'lunas') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                        @elseif($item->status_kredit === 'macet') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                        @endif">
                                        {{ ucfirst($item->status_kredit) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada data kredit elektronik
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>