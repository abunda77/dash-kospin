<x-filament-panels::page>
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-primary-100 dark:bg-primary-900/20">
                    <x-heroicon-o-banknotes class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Rekening</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getJumlahTabungan() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-success-100 dark:bg-success-900/20">
                    <x-heroicon-o-currency-dollar class="w-8 h-8 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Saldo Aktif</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $this->getTotalSaldo() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        {{ $this->table }}
    </div>

    {{-- Detail Modal --}}
    @if($selectedTabungan)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" 
                     wire:click="closeDetail"></div>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Detail Tabungan
                            </h3>
                            <button type="button" 
                                    wire:click="closeDetail"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>
                        
                        {{ $this->tabunganInfolist }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
