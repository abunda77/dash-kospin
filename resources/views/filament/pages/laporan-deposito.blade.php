<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        <x-filament::section>
            <x-slot name="heading">
                Filter Laporan
            </x-slot>
            
            {{ $this->form }}
            
            <div class="mt-4 flex flex-wrap gap-3">
                <x-filament::button 
                    wire:click="cetakPDF"
                    icon="heroicon-o-printer"
                    color="primary"
                >
                    Cetak PDF
                </x-filament::button>
                
                <x-filament::button 
                    wire:click="$refresh"
                    icon="heroicon-o-arrow-path"
                    color="gray"
                >
                    Refresh Data
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Informasi Tambahan
            </x-slot>
            
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="flex flex-col gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Status Deposito</h3>
                    <div class="flex flex-col gap-2">
                        @php
                            $statusCounts = $this->getBaseQuery()
                                ->selectRaw('status, COUNT(*) as count')
                                ->groupBy('status')
                                ->pluck('count', 'status');
                        @endphp
                        
                        @foreach(['active' => 'Aktif', 'ended' => 'Berakhir', 'cancelled' => 'Dibatalkan'] as $key => $label)
                            <div class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $statusCounts[$key] ?? 0 }} deposito</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="flex flex-col gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Deposito Jatuh Tempo</h3>
                    <div class="flex flex-col gap-2">
                        @php
                            $jatuhTempo = [
                                'hari_ini' => \App\Models\Deposito::where('tanggal_jatuh_tempo', today())->count(),
                                'minggu_ini' => \App\Models\Deposito::whereBetween('tanggal_jatuh_tempo', [
                                    now()->startOfWeek(), 
                                    now()->endOfWeek()
                                ])->count(),
                                'bulan_ini' => \App\Models\Deposito::whereBetween('tanggal_jatuh_tempo', [
                                    now()->startOfMonth(), 
                                    now()->endOfMonth()
                                ])->count(),
                            ];
                        @endphp
                        
                        <div class="flex items-center justify-between gap-4 rounded-lg bg-red-50 p-3 dark:bg-red-950/40">
                            <span class="font-medium text-gray-900 dark:text-gray-100">Hari Ini</span>
                            <span class="text-sm text-red-700 dark:text-red-300">{{ $jatuhTempo['hari_ini'] }} deposito</span>
                        </div>
                        
                        <div class="flex items-center justify-between gap-4 rounded-lg bg-yellow-50 p-3 dark:bg-yellow-950/40">
                            <span class="font-medium text-gray-900 dark:text-gray-100">Minggu Ini</span>
                            <span class="text-sm text-yellow-700 dark:text-yellow-300">{{ $jatuhTempo['minggu_ini'] }} deposito</span>
                        </div>
                        
                        <div class="flex items-center justify-between gap-4 rounded-lg bg-blue-50 p-3 dark:bg-blue-950/40">
                            <span class="font-medium text-gray-900 dark:text-gray-100">Bulan Ini</span>
                            <span class="text-sm text-blue-700 dark:text-blue-300">{{ $jatuhTempo['bulan_ini'] }} deposito</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
