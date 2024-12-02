<x-filament-panels::page>
    <form wire:submit="search">
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

    @if($pinjaman)
        <div class="p-6 mt-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Informasi Pinjaman</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-700 dark:text-white"><strong>Nama Peminjam:</strong> {{ $pinjaman->profile->first_name }} {{ $pinjaman->profile->last_name }}</p>
                    <p class="text-gray-700 dark:text-white"><strong>No. Pinjaman:</strong> {{ $pinjaman->no_pinjaman }}</p>
                    <p class="text-gray-700 dark:text-white"><strong>Jumlah Pinjaman:</strong> Rp {{ number_format($pinjaman->jumlah_pinjaman, 2) }}</p>
                    <p class="text-gray-700 dark:text-white"><strong>Jangka Waktu:</strong> {{ $pinjaman->jangka_waktu }} {{ $pinjaman->jangka_waktu_satuan }}</p>
                </div>
                <div>
                    <p class="text-gray-700 dark:text-white"><strong>Bunga:</strong> {{ $pinjaman->biayaBungaPinjaman->persentase_bunga }}%</p>
                    <p class="text-gray-700 dark:text-white"><strong>Awal Periode:</strong>
                        {{ $pinjaman->tanggal_pinjaman ? $pinjaman->tanggal_pinjaman->format('d/m/Y') : '-' }}
                    </p>
                    <p class="text-gray-700 dark:text-white"><strong>Akhir Periode:</strong>
                        {{ $pinjaman->tanggal_jatuh_tempo ? $pinjaman->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Tabel Angsuran</h2>
            <div class="overflow-x-auto">
                <table class="w-full border border-collapse border-gray-200 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Periode</th>
                            <th class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Tanggal Jatuh Tempo</th>
                            <th class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Pokok</th>
                            <th class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Bunga</th>
                            <th class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Angsuran</th>
                            <th class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Sisa Pokok</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @foreach($angsuranList as $angsuran)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">{{ $angsuran['periode'] }}</td>
                                <td class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">{{ $angsuran['tanggal_jatuh_tempo'] }}</td>
                                <td class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Rp {{ number_format($angsuran['pokok'], 2) }}</td>
                                <td class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Rp {{ number_format($angsuran['bunga'], 2) }}</td>
                                <td class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Rp {{ number_format($angsuran['angsuran'], 2) }}</td>
                                <td class="p-2 text-gray-900 border border-gray-200 dark:border-gray-700 dark:text-white">Rp {{ number_format($angsuran['sisa_pokok'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
