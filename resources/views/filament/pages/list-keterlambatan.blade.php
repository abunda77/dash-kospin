<x-filament-panels::page>
    {{-- Judul halaman --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight">
            Daftar Keterlambatan Pembayaran Angsuran Bulan Ini
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Menampilkan daftar anggota yang terlambat melakukan pembayaran angsuran bulan ini
        </p>
    </div>

    {{-- Debug data --}}
    @php
        $data = $this->getData();
        Log::info('Data count: ' . $data->count());
        foreach($data as $item) {
            Log::info('Record:', [
                'id' => $item->id_pinjaman,
                'jumlah_pinjaman' => $item->jumlah_pinjaman,
                'jangka_waktu' => $item->jangka_waktu,
                'status' => $item->status_pinjaman
            ]);
        }
    @endphp

    <div class="flex justify-end mb-4">
        <x-filament::button
            wire:click="print"
            icon="heroicon-o-printer"
        >
            Cetak PDF
        </x-filament::button>
    </div>

    {{-- Tabel keterlambatan --}}
    <div class="space-y-4">
        {{ $this->table }}
    </div>

    {{-- Tambahkan informasi tambahan jika diperlukan --}}
    <div class="mt-4 text-sm text-gray-500">
        <p>* Total Bayar = Angsuran Pokok + Bunga + Denda</p>
    </div>
</x-filament-panels::page>
