<x-filament-panels::page>
    {{ $this->form }}

    @if($isSearchSubmitted)
        <div class="mt-4">
            <div class="mb-4">
                @if($tabungan)
                    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Nama</p>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $tabungan->profile?->first_name }} {{ $tabungan->profile?->last_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Nomor Rekening</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $tabungan->no_tabungan }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Jenis Tabungan</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $tabungan->produkTabungan?->nama_produk }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Saldo</p>
                                <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($saldo_berjalan, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-4 mt-4 mb-4 space-x-2">
                <x-filament::button wire:click="print" color="success">
                    Cetak Buku
                </x-filament::button>
                <x-filament::button wire:click="printTable" color="warning">
                    Cetak Tabel
                </x-filament::button>
            </div>

            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
