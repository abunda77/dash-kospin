<x-filament-panels::page>
    <div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <form wire:submit="search" class="space-y-6">
            {{ $this->form }}

            <div class="flex gap-4 mt-4">
                <x-filament::button type="submit" size="lg" wire:loading.attr="disabled" class="dark:bg-primary-600 dark:hover:bg-primary-500 dark:text-white">
                    <span wire:loading.remove>
                        Cari Mutasi
                    </span>
                    <span wire:loading>
                        <svg class="inline w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </x-filament::button>

                <x-filament::button color="danger" wire:click="clearSearch" type="button" size="lg" wire:loading.attr="disabled" class="dark:bg-primary-600 dark:hover:bg-primary-500 dark:text-white">
                    <span wire:loading.remove>
                        Reset
                    </span>
                    <span wire:loading>
                        <svg class="inline w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </x-filament::button>
            </div>
        </form>
    </div>

    @if($isSearchSubmitted && $tabungan)

        <div class="mt-4">
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nama</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $tabungan->profile?->first_name  ?? '-' }} {{ $tabungan->profile?->last_name  ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Nomor Rekening</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $tabungan->no_tabungan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Jenis Tabungan</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $tabungan->produkTabungan?->nama_produk ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Saldo</p>
                        <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($tabungan->saldo ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-4 mt-4">
            <x-filament::button
                wire:click="print"
                icon="heroicon-o-printer"
                color="success"
            >
                Cetak Mutasi
            </x-filament::button>
            <x-filament::button
                wire:click="printTable"
                icon="heroicon-o-document"
            >
                Cetak Tabel
            </x-filament::button>
        </div>
        <div class="mt-4">
            {{ $this->table }}
        </div>
    @endif


</x-filament-panels::page>
