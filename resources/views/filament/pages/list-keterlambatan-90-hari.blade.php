<x-filament-panels::page>
    {{-- Header Stats --}}
    <div class="grid gap-4 md:gap-6 lg:gap-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @foreach ($this->getStatsWidgets() as $widget)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-200">{{ $widget->getLabel() }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $widget->getValue() }}</p>
                            @if ($widget->getDescription())
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $widget->getDescription() }}</p>
                            @endif
                        </div>
                        @if ($widget->getIcon())
                            <div class="ml-4">
                                <x-filament::icon
                                    :icon="$widget->getIcon()"
                                    class="w-8 h-8 text-{{ $widget->getColor() }}-500 dark:text-{{ $widget->getColor() }}-400"
                                />
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Alert Section --}}
    <div class="mt-8">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <x-filament::icon 
                    icon="heroicon-o-exclamation-triangle" 
                    class="w-6 h-6 text-red-600 mr-3 mt-0.5" 
                />
                <div>
                    <h3 class="text-sm font-medium text-red-800">
                        Peringatan Keterlambatan Kritis
                    </h3>
                    <p class="text-sm text-red-700 mt-1">
                        Daftar di bawah ini menampilkan seluruh pinjaman yang telah mengalami keterlambatan lebih dari 90 hari. 
                        Tindakan penagihan segera diperlukan untuk mencegah kerugian yang lebih besar.
                    </p>
                    <div class="mt-3 flex space-x-4">
                        <button 
                            type="button" 
                            wire:click="print"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            <x-filament::icon icon="heroicon-o-printer" class="w-4 h-4 mr-2" />
                            Cetak Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="mt-8">
        {{ $this->table }}
    </div>

    {{-- Additional Info Section --}}
    <div class="mt-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start">
                <x-filament::icon 
                    icon="heroicon-o-information-circle" 
                    class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" 
                />
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">
                        Informasi Perhitungan
                    </h3>
                    <ul class="text-sm text-yellow-700 mt-1 space-y-1">
                        <li>• <strong>Denda Harian:</strong> 5% dari angsuran total dibagi 30 hari</li>
                        <li>• <strong>Total Tunggakan:</strong> Angsuran pokok + bunga + denda yang belum dibayar</li>
                        <li>• <strong>Periode Keterlambatan:</strong> Dihitung dari tanggal jatuh tempo terakhir</li>
                        <li>• <strong>Status Kritis:</strong> Keterlambatan > 90 hari memerlukan tindakan khusus</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for downloading --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('download-file', (event) => {
                const { url, filename } = event;
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
</x-filament-panels::page>
