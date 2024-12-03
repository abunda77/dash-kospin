<x-filament::page>
    <div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <form wire:submit="search" class="space-y-6">
            {{ $this->form }}

            <div class="flex gap-4 mt-4">
                <x-filament::button type="submit">
                    Cari
                </x-filament::button>

                <x-filament::button color="danger" wire:click="clearSearch" type="button">
                    Reset
                </x-filament::button>
            </div>
        </form>
    </div>

    @if($pinjaman)
        <div class="flex justify-end gap-4 mt-4">
            <x-filament::button
                wire:click="printSimulasi"
                icon="heroicon-o-document"
                color="success"
            >
                Cetak Simulasi
            </x-filament::button>

            <x-filament::button
                wire:click="print"
                icon="heroicon-o-printer"
                color="warning"
            >
                Cetak Riwayat
            </x-filament::button>
        </div>

        <div class="p-6 mt-8 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="info">
                <table style="width: 75%; border: none;">
                    <tr>
                        <td style="border: none;">Nama</td>
                        <td style="border: none;">: {{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Alamat</td>
                        <td style="border: none;">: {{ $pinjaman->profile->address }}, {{ \App\Models\Region::where('code', $pinjaman->profile->village_id)->first()?->name }}, {{ \App\Models\Region::where('code', $pinjaman->profile->district_id)->first()?->name }}, {{ \App\Models\Region::where('code', $pinjaman->profile->city_id)->first()?->name }}, {{ \App\Models\Region::where('code', $pinjaman->profile->province_id)->first()?->name }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">No Pinjaman</td>
                        <td style="border: none;">: {{ $pinjaman->no_pinjaman }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Produk Pinjaman</td>
                        <td style="border: none;">: {{ $pinjaman->produkPinjaman->nama_produk }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Jumlah Pinjaman</td>
                        <td style="border: none;">: Rp {{ number_format($pinjaman->jumlah_pinjaman, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Tanggal Pinjaman</td>
                        <td style="border: none;">: {{ $pinjaman->tanggal_pinjaman->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Jangka Waktu</td>
                        <td style="border: none;">: {{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}</td>
                    </tr>
                </table>

                <div class="mt-4">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Foto Identitas</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @if($pinjaman->profile->image_identity)
                            @foreach($pinjaman->profile->image_identity as $image)
                                <img src="{{ Storage::url($image) }}" alt="Foto Identitas" class="object-cover w-12 h-auto rounded-lg">
                            @endforeach
                        @else
                            <div class="flex items-center justify-center w-full h-32 bg-gray-100 rounded-lg dark:bg-gray-700">
                                <span class="text-gray-500 dark:text-gray-400">Tidak ada foto identitas</span>
                            </div>
                        @endif
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
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp. {{ number_format($angsuran['pokok'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp. {{ number_format($angsuran['bunga'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp. {{ number_format($angsuran['angsuran'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">Rp. {{ number_format($angsuran['sisa_pokok'], 2) }}</td>
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
