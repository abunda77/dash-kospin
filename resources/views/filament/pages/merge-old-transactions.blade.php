<x-filament-panels::page>
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
        <h2 class="text-lg font-medium">Informasi Tabungan</h2>
        <div class="grid grid-cols-2 gap-4 mt-4">
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
        </div>
    </div>

    <div class="p-4 mt-8 border border-yellow-200 rounded-lg bg-yellow-50">
        <div class="flex items-center gap-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-500"/>
            <h3 class="text-lg font-medium text-yellow-800">Perhatian</h3>
        </div>
        <p class="mt-2 text-yellow-700">
            Tindakan ini akan menggabungkan semua transaksi sebelum tahun {{ now()->subYear()->startOfYear()->format('Y') }}
            menjadi satu transaksi pembuka. Proses ini tidak dapat dibatalkan.
        </p>
    </div>

    <div class="flex justify-end gap-4 mt-8">
        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.pages.mutasi-tabungan-v2') }}"
            color="gray"
        >
            Kembali
        </x-filament::button>

        <x-filament::button
            wire:click="mergeTransactions"
            color="warning"
        >
            Gabung Transaksi
        </x-filament::button>
    </div>
</x-filament-panels::page>
