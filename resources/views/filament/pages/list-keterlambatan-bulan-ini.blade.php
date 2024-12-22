<x-filament-panels::page>
    <div class="flex justify-end mb-4">
        <x-filament::button
            wire:click="print"
            icon="heroicon-o-printer"
            class="mr-2"
        >
            Cetak Laporan
        </x-filament::button>
    </div>

    {{ $this->table }}

    <div class="mt-4">
        <div class="text-sm text-gray-600">
            <p>Keterangan:</p>
            <ul class="list-disc list-inside">
                <li>Data menampilkan daftar pinjaman yang terlambat lebih dari 30 hari</li>
                <li>Bunga dihitung berdasarkan persentase bunga tahunan dibagi jangka waktu</li>
                <li>Denda dihitung berdasarkan rate denda yang berlaku</li>
                <li>Total bayar adalah jumlah angsuran pokok ditambah bunga dan denda</li>
                <li>Klik icon WhatsApp untuk mengirim pesan pengingat</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
