<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <form wire:submit="cekSaldo">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit">
                        Cek Saldo
                    </x-filament::button>
                </div>
            </form>
        </div>

        @if($tabungan)
            <div class="p-6 bg-white shadow rounded-xl dark:bg-gray-800">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
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
