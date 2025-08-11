<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Laporan</h3>
            {{ $this->form }}
            
            <div class="mt-4 flex gap-4">
                <x-filament::button wire:click="cetakPDF" color="primary">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                    Cetak PDF
                </x-filament::button>
            </div>
        </div>

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php $stats = $this->getStatsData(); @endphp
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-sparkles class="w-8 h-8 text-green-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($stats['total_cicilan']) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Total Cicilan Emas
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-banknotes class="w-8 h-8 text-blue-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($stats['total_harga'], 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Total Nilai Cicilan
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-arrow-down-circle class="w-8 h-8 text-cyan-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($stats['total_setoran_awal'], 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Total Setoran Awal
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-calculator class="w-8 h-8 text-yellow-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($stats['total_biaya_admin'], 0, ',', '.') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Total Biaya Admin
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-scale class="w-8 h-8 text-gray-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($stats['rata_rata_berat'], 2) }} gram
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Rata-rata Berat Emas
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-play-circle class="w-8 h-8 text-green-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($stats['aktif_count']) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Cicilan Aktif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($stats['lunas_count']) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Cicilan Lunas
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-x-circle class="w-8 h-8 text-red-500" />
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($stats['gagal_bayar_count']) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Gagal Bayar
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Trend Cicilan Emas Bulanan</h3>
                <div class="h-64">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Distribusi Status Cicilan</h3>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Data Cicilan Emas</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Anggota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Berat Emas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Setoran Awal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Biaya Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($this->getBaseQuery()->get() as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $item->no_transaksi }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $item->pinjaman->profile->nama_lengkap ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ number_format($item->berat_emas, 3) }} gram
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ format_rupiah($item->total_harga) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ format_rupiah($item->setoran_awal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ format_rupiah($item->biaya_admin) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($item->status === 'aktif') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($item->status === 'lunas') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($item->status === 'gagal_bayar') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada data cicilan emas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Trend Chart
            const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
            if (monthlyTrendCtx) {
                const chartData = @json($this->getChartData());
                new Chart(monthlyTrendCtx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusData = @json($this->getStatusChartData());
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        });

        // Refresh charts when filters change
        document.addEventListener('livewire:updated', function() {
            // Destroy existing charts
            Chart.helpers.each(Chart.instances, function(instance) {
                instance.destroy();
            });

            // Recreate charts with new data
            setTimeout(function() {
                // Monthly Trend Chart
                const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
                if (monthlyTrendCtx) {
                    const chartData = @json($this->getChartData());
                    new Chart(monthlyTrendCtx, {
                        type: 'line',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // Status Distribution Chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    const statusData = @json($this->getStatusChartData());
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: statusData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                }
            }, 100);
        });
    </script>
    @endpush
</x-filament-panels::page>