<x-filament-panels::page>
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-primary-100 dark:bg-primary-900/20">
                    <x-heroicon-o-building-library class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Deposito</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getJumlahDeposito() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-success-100 dark:bg-success-900/20">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Deposito Aktif</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $this->getDepositoAktif() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-info-100 dark:bg-info-900/20">
                    <x-heroicon-o-banknotes class="w-8 h-8 text-info-600 dark:text-info-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Penempatan</p>
                    <p class="text-lg font-bold text-info-600 dark:text-info-400">{{ $this->getTotalDeposito() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-warning-100 dark:bg-warning-900/20">
                    <x-heroicon-o-chart-bar class="w-8 h-8 text-warning-600 dark:text-warning-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bunga</p>
                    <p class="text-lg font-bold text-warning-600 dark:text-warning-400">{{ $this->getTotalBunga() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-danger-100 dark:bg-danger-900/20">
                    <x-heroicon-o-clock class="w-8 h-8 text-danger-600 dark:text-danger-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jatuh Tempo Bulan Ini</p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $this->getDepositoJatuhTempoBulanIni() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        {{ $this->table }}
    </div>

    {{-- Detail Modal --}}
    @if($selectedDeposito)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" 
                     wire:click="closeDetail"></div>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4 sticky top-0 bg-white dark:bg-gray-800 py-2 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-heroicon-o-building-library class="w-6 h-6" />
                                Detail Deposito
                            </h3>
                            <button type="button" 
                                    wire:click="closeDetail"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>
                        
                        {{ $this->depositoInfolist }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
