<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <x-filament::section>
            <x-slot name="heading">
                Filter Laporan
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{ $this->form }}
            </div>
            
            <div class="flex gap-4 mt-4">
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

        <!-- Stats and Charts -->
        <div class="space-y-6">
            @foreach ($this->getHeaderWidgets() as $widget)
                @livewire($widget, ['page' => $this])
            @endforeach
        </div>

        <!-- Additional Information -->
        <x-filament::section>
            <x-slot name="heading">
                Informasi Tambahan
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Status Deposito</h3>
                    <div class="space-y-2">
                        @php
                            $statusCounts = $this->getBaseQuery()
                                ->selectRaw('status, COUNT(*) as count')
                                ->groupBy('status')
                                ->pluck('count', 'status');
                        @endphp
                        
                        @foreach(['active' => 'Aktif', 'ended' => 'Berakhir', 'cancelled' => 'Dibatalkan'] as $key => $label)
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                                <span class="text-sm text-gray-300 dark:text-gray-100">{{ $statusCounts[$key] ?? 0 }} deposito</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Deposito Jatuh Tempo</h3>
                    <div class="space-y-2">
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
                        
                        <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                            <span class="font-medium">Hari Ini</span>
                            <span class="text-sm text-red-600">{{ $jatuhTempo['hari_ini'] }} deposito</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                            <span class="font-medium">Minggu Ini</span>
                            <span class="text-sm text-yellow-600">{{ $jatuhTempo['minggu_ini'] }} deposito</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                            <span class="font-medium">Bulan Ini</span>
                            <span class="text-sm text-blue-600">{{ $jatuhTempo['bulan_ini'] }} deposito</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>