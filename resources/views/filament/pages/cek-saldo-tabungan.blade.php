<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <form wire:submit="cekSaldo">
                {{ $this->form }}

                <div class="mt-6 text-right">
                    <x-filament::button type="submit" color="success" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            Cek Saldo Akhir
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

        @if($tabungan)
            <div class="p-6 bg-white shadow rounded-xl dark:bg-gray-800">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-200">
                            Saldo Awal
                        </h3>
                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            Rp {{ number_format($saldo_awal, 2, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Saldo Akhir
                        </h3>
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            Rp {{ number_format($saldo_akhir, 2, ',', '.') }}
                        </p>
                    </div>

                    <div class="pt-4 mt-4 text-sm text-gray-600 border-t border-gray-200 dark:text-gray-400 dark:border-gray-700">
                        Terakhir diperbarui: {{ now()->format('d M Y H:i') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
