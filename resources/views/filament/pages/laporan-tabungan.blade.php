<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Laporan</h3>
            {{ $this->form }}
        </div>

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($this->getStatsWidgets() as $widget)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $widget->getValue() }}
                            </div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{ $widget->getLabel() }}
                            </div>
                            @if ($widget->getDescription())
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $widget->getDescription() }}
                                </div>
                            @endif
                        </div>
                        @if ($widget->getIcon())
                            <div class="ml-4">
                                <x-heroicon-o-banknotes class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Product Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Distribusi Produk Tabungan</h3>
                    <button id="refreshProductChart" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </button>
                </div>
                <div class="h-64 relative">
                    <div id="productChartLoader" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <canvas id="productChart"></canvas>
                </div>
                <div id="productChartLegend" class="mt-4 text-sm text-gray-600 dark:text-gray-400"></div>
            </div>

            <!-- Monthly Trends Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tren Bulanan Transaksi</h3>
                    <button id="refreshTrendsChart" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </button>
                </div>
                <div class="h-64 relative">
                    <div id="trendsChartLoader" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>

            <!-- Balance Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Distribusi Saldo</h3>
                    <button id="refreshBalanceChart" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </button>
                </div>
                <div class="h-64 relative">
                    <div id="balanceChartLoader" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <canvas id="balanceChart"></canvas>
                </div>
            </div>
        </div>        <!-- Additional Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Transaction Types Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Jenis Transaksi</h3>
                    <button id="refreshTransactionChart" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </button>
                </div>
                <div class="h-64 relative">
                    <div id="transactionChartLoader" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <canvas id="transactionChart"></canvas>
                </div>
            </div>

            <!-- Account Growth Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pertumbuhan Rekening</h3>
                    <button id="refreshGrowthChart" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </button>
                </div>
                <div class="h-64 relative">
                    <div id="growthChartLoader" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <canvas id="growthChart"></canvas>
                </div>
            </div>

            <!-- Top Savers Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Top 10 Nasabah</h3>
                    <button id="refreshTopSaversChart" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                    </button>
                </div>
                <div class="h-64 relative">
                    <div id="topSaversChartLoader" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                    <canvas id="topSaversChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Data Tabungan</h3>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        // Chart instances to manage memory
        let productChart = null;
        let trendsChart = null;
        let balanceChart = null;
        let transactionChart = null;
        let growthChart = null;

        // Chart color schemes
        const colors = {
            primary: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'],
            gradient: ['#60A5FA', '#34D399', '#FBBF24', '#F87171', '#A78BFA', '#22D3EE', '#A3E635', '#FB923C'],
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444',
            info: '#3B82F6'
        };

        function showLoader(chartId) {
            document.getElementById(chartId + 'Loader').classList.remove('hidden');
        }

        function hideLoader(chartId) {
            document.getElementById(chartId + 'Loader').classList.add('hidden');
        }

        function destroyChart(chart) {
            if (chart) {
                chart.destroy();
            }
        }

        function createProductChart() {
            showLoader('productChart');
            
            try {
                const productData = @json($this->getProductDistribution());
                const productCtx = document.getElementById('productChart').getContext('2d');
                
                // Destroy existing chart if it exists
                destroyChart(productChart);
                
                productChart = new Chart(productCtx, {
                    type: 'doughnut',
                    data: {
                        labels: productData.map(item => item.product),
                        datasets: [{
                            data: productData.map(item => item.count),
                            backgroundColor: colors.primary,
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverBorderWidth: 4,
                            hoverBackgroundColor: colors.gradient
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: { size: 12 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = productData[context.dataIndex];
                                        const percentage = ((context.parsed / productData.reduce((sum, p) => sum + p.count, 0)) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' rekening (' + percentage + '%) - Rp ' + 
                                               new Intl.NumberFormat('id-ID').format(item.total_balance);
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1000
                        }
                    }
                });
                
                // Update legend with additional info
                updateProductLegend(productData);
                
            } catch (error) {
                console.error('Error creating product chart:', error);
            } finally {
                hideLoader('productChart');
            }
        }

        function updateProductLegend(data) {
            const legendEl = document.getElementById('productChartLegend');
            const total = data.reduce((sum, item) => sum + item.count, 0);
            const totalBalance = data.reduce((sum, item) => sum + item.total_balance, 0);
            
            legendEl.innerHTML = `
                <div class="text-center">
                    <div class="font-medium">Total: ${total} rekening</div>
                    <div class="text-xs">Saldo: Rp ${new Intl.NumberFormat('id-ID').format(totalBalance)}</div>
                </div>
            `;
        }        function createTrendsChart() {
            showLoader('trendsChart');
            
            try {
                const trendsData = @json($this->getMonthlySavingsTrends());
                const trendsCtx = document.getElementById('trendsChart').getContext('2d');
                
                // Destroy existing chart if it exists
                destroyChart(trendsChart);
                
                const gradient1 = trendsCtx.createLinearGradient(0, 0, 0, 400);
                gradient1.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
                gradient1.addColorStop(1, 'rgba(16, 185, 129, 0.05)');
                
                const gradient2 = trendsCtx.createLinearGradient(0, 0, 0, 400);
                gradient2.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
                gradient2.addColorStop(1, 'rgba(245, 158, 11, 0.05)');
                
                const gradient3 = trendsCtx.createLinearGradient(0, 0, 0, 400);
                gradient3.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
                gradient3.addColorStop(1, 'rgba(59, 130, 246, 0.05)');
                
                trendsChart = new Chart(trendsCtx, {
                    type: 'line',
                    data: {
                        labels: trendsData.map(item => {
                            const date = new Date(item.month + '-01');
                            return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short' });
                        }),
                        datasets: [
                            {
                                label: 'Setoran',
                                data: trendsData.map(item => item.deposits),
                                borderColor: colors.success,
                                backgroundColor: gradient1,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: colors.success,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8
                            },
                            {
                                label: 'Penarikan',
                                data: trendsData.map(item => item.withdrawals),
                                borderColor: colors.warning,
                                backgroundColor: gradient2,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: colors.warning,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8
                            },
                            {
                                label: 'Net Flow',
                                data: trendsData.map(item => item.net_flow),
                                borderColor: colors.info,
                                backgroundColor: gradient3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: colors.info,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'top',
                                labels: { font: { size: 12 } }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': Rp ' + 
                                               new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                title: { display: true, text: 'Bulan' },
                                grid: { display: false }
                            },
                            y: {
                                display: true,
                                title: { display: true, text: 'Jumlah (Rp)' },
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                            notation: 'compact',
                                            compactDisplay: 'short'
                                        }).format(value);
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
                
            } catch (error) {
                console.error('Error creating trends chart:', error);
            } finally {
                hideLoader('trendsChart');
            }
        }        function createBalanceChart() {
            showLoader('balanceChart');
            
            try {
                const balanceData = @json($this->getBalanceDistribution());
                
                const balanceCtx = document.getElementById('balanceChart').getContext('2d');
                destroyChart(balanceChart);
                
                balanceChart = new Chart(balanceCtx, {
                    type: 'bar',
                    data: {
                        labels: balanceData.map(item => item.range),
                        datasets: [{
                            label: 'Jumlah Rekening',
                            data: balanceData.map(item => item.count),
                            backgroundColor: colors.primary,
                            borderWidth: 0,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Rekening: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10,
                                    callback: function(value) {
                                        return value + ' rekening';
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutBounce'
                        }
                    }
                });
                
            } catch (error) {
                console.error('Error creating balance chart:', error);
            } finally {
                hideLoader('balanceChart');
            }
        }

        function createTransactionChart() {
            showLoader('transactionChart');
            
            try {
                const stats = @json($this->getStatsData());
                
                const transactionCtx = document.getElementById('transactionChart').getContext('2d');
                destroyChart(transactionChart);
                
                transactionChart = new Chart(transactionCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Setoran', 'Penarikan'],
                        datasets: [{
                            data: [stats.deposit_count, stats.withdrawal_count],
                            backgroundColor: [colors.success, colors.warning],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverBorderWidth: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = stats.deposit_count + stats.withdrawal_count;
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' transaksi (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 1000
                        }
                    }
                });
                
            } catch (error) {
                console.error('Error creating transaction chart:', error);
            } finally {
                hideLoader('transactionChart');
            }
        }        function createGrowthChart() {
            showLoader('growthChart');
            
            try {
                const growthData = @json($this->getAccountGrowth());
                
                const growthCtx = document.getElementById('growthChart').getContext('2d');
                destroyChart(growthChart);
                
                growthChart = new Chart(growthCtx, {
                    type: 'bar',
                    data: {
                        labels: growthData.map(item => {
                            const date = new Date(item.month + '-01');
                            return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short' });
                        }),
                        datasets: [
                            {
                                label: 'Rekening Baru',
                                data: growthData.map(item => item.new_accounts),
                                backgroundColor: colors.success,
                                borderRadius: 4,
                                borderSkipped: false
                            },
                            {
                                label: 'Rekening Tutup',
                                data: growthData.map(item => -item.closed_accounts),
                                backgroundColor: colors.danger,
                                borderRadius: 4,
                                borderSkipped: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { size: 12 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + Math.abs(context.parsed.y) + ' rekening';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        return Math.abs(value);
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
                
            } catch (error) {
                console.error('Error creating growth chart:', error);
            } finally {
                hideLoader('growthChart');
            }
        }

        function initializeCharts() {
            // Use requestAnimationFrame for better performance
            requestAnimationFrame(() => {
                createProductChart();
                createTrendsChart();
                createBalanceChart();
                createTransactionChart();
                createGrowthChart();
            });
        }

        function setupRefreshButtons() {
            document.getElementById('refreshProductChart')?.addEventListener('click', createProductChart);
            document.getElementById('refreshTrendsChart')?.addEventListener('click', createTrendsChart);
            document.getElementById('refreshBalanceChart')?.addEventListener('click', createBalanceChart);
            document.getElementById('refreshTransactionChart')?.addEventListener('click', createTransactionChart);
            document.getElementById('refreshGrowthChart')?.addEventListener('click', createGrowthChart);
        }

        // Initialize charts when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            setupRefreshButtons();
        });

        // Listen for Livewire updates without reloading page
        document.addEventListener('livewire:navigated', function() {
            // Debounce chart updates to prevent excessive calls
            setTimeout(() => {
                initializeCharts();
                setupRefreshButtons();
            }, 300);
        });

        // Listen for form updates that might affect charts
        window.addEventListener('livewire:updated', function() {
            setTimeout(initializeCharts, 500);
        });

        // Clean up charts when page is unloaded
        window.addEventListener('beforeunload', function() {
            destroyChart(productChart);
            destroyChart(trendsChart);
            destroyChart(balanceChart);
            destroyChart(transactionChart);
            destroyChart(growthChart);
        });

        // Responsive chart resize handler
        window.addEventListener('resize', function() {
            setTimeout(() => {
                [productChart, trendsChart, balanceChart, transactionChart, growthChart].forEach(chart => {
                    if (chart) {
                        chart.resize();
                    }
                });
            }, 100);
        });
    </script>
    @endpush
</x-filament-panels::page>