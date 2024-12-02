<x-filament::page>
    <div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <form wire:submit="search" class="space-y-6">
            {{ $this->form }}

            <div class="flex gap-4">
                <x-filament::button type="submit" class="bg-primary-600 hover:bg-primary-500 dark:bg-primary-700 dark:hover:bg-primary-600">
                    Cari
                </x-filament::button>

                <x-filament::button wire:click="clearSearch" class="bg-danger-500 hover:bg-danger-400 dark:bg-danger-600 dark:hover:bg-danger-500">
                    Reset
                </x-filament::button>
            </div>
        </form>
    </div>

    @if($pinjaman)
        <div class="p-6 mt-8 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Peminjam</h3>
                    <div class="space-y-2">
                        <div class="flex">
                            <span class="w-40 text-gray-600 dark:text-gray-400">Nama</span>
                            <span class="text-gray-900 dark:text-gray-100">: {{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600 dark:text-gray-400">No Pinjaman</span>
                            <span class="text-gray-900 dark:text-gray-100">: {{ $pinjaman->no_pinjaman }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600 dark:text-gray-400">Produk Pinjaman</span>
                            <span class="text-gray-900 dark:text-gray-100">: {{ $pinjaman->produkPinjaman->nama_produk }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600 dark:text-gray-400">Jumlah Pinjaman</span>
                            <span class="text-gray-900 dark:text-gray-100">: Rp {{ number_format($pinjaman->jumlah_pinjaman, 2) }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600 dark:text-gray-400">Tanggal Pinjaman</span>
                            <span class="text-gray-900 dark:text-gray-100">: {{ $pinjaman->tanggal_pinjaman->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-40 text-gray-600 dark:text-gray-400">Jangka Waktu</span>
                            <span class="text-gray-900 dark:text-gray-100">: {{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 overflow-hidden bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Periode</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Pokok</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Bunga</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Total Angsuran</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Sisa Pokok</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Jatuh Tempo</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach($angsuranList as $index => $angsuran)
                        <tr class="transition duration-150 hover:bg-gray-300 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $angsuran['periode'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp {{ number_format($angsuran['pokok'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp {{ number_format($angsuran['bunga'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp {{ number_format($angsuran['angsuran'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp {{ number_format($angsuran['sisa_pokok'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $angsuran['tanggal_jatuh_tempo'] }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <x-filament::button
                                    wire:click="bayarAngsuran({{ $angsuran['periode'] }})"
                                    size="sm"
                                    :disabled="$this->isAngsuranPaid($angsuran['periode'])"
                                    class="transition duration-150 {{ $this->isAngsuranPaid($angsuran['periode'])
                                        ? 'bg-gray-400 cursor-not-allowed'
                                        : 'bg-success-600 hover:bg-success-500 dark:bg-success-700 dark:hover:bg-success-600' }}">
                                    {{ $this->isAngsuranPaid($angsuran['periode']) ? 'Sudah Dibayar' : 'Bayar' }}
                                </x-filament::button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-6 mt-8 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <h3 class="mb-6 text-lg font-medium text-gray-900 dark:text-gray-100">History Pembayaran</h3>
            {{ $this->table }}
        </div>
    @endif
</x-filament::page>
