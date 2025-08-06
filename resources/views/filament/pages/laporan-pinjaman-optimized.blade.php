<x-filament-panels::page>
    {{-- Form Filter --}}
    <x-filament-panels::form wire:submit="submit">
        {{ $this->form }}
    </x-filament-panels::form>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach($this->getStatsWidgets() as $stat)
            <div wire:loading.class="opacity-50" wire:target="data.period,data.productFilter,data.dateRange">
                {{ $stat }}
            </div>
        @endforeach
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
        {{-- Payment Trends Chart --}}
        <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Tren Pembayaran
                </h3>
            </div>
            <div class="p-4">
                <div wire:loading wire:target="data.period,data.productFilter,data.dateRange">
                    <div class="flex items-center justify-center h-64">
                        <x-filament::loading-indicator class="w-8 h-8" />
                        <span class="ml-2 text-gray-500">Memuat data...</span>
                    </div>
                </div>
                <div wire:loading.remove wire:target="data.period,data.productFilter,data.dateRange">
                    <canvas id="paymentTrendsChart" class="w-full h-64"></canvas>
                </div>
            </div>
        </div>

        {{-- Product Distribution Chart --}}
        <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Distribusi Produk
                </h3>
            </div>
            <div class="p-4">
                <div wire:loading wire:target="data.period,data.productFilter,data.dateRange">
                    <div class="flex items-center justify-center h-64">
                        <x-filament::loading-indicator class="w-8 h-8" />
                        <span class="ml-2 text-gray-500">Memuat data...</span>
                    </div>
                </div>
                <div wire:loading.remove wire:target="data.period,data.productFilter,data.dateRange">
                    <canvas id="productDistributionChart" class="w-full h-64"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Loan Trends Chart --}}
    <div class="mt-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="p-4 border-b dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Tren Pinjaman Bulanan
            </h3>
        </div>
        <div class="p-4">
            <div wire:loading wire:target="data.period,data.productFilter,data.dateRange">
                <div class="flex items-center justify-center h-64">
                    <x-filament::loading-indicator class="w-8 h-8" />
                    <span class="ml-2 text-gray-500">Memuat data...</span>
                </div>
            </div>
            <div wire:loading.remove wire:target="data.period,data.productFilter,data.dateRange">
                <canvas id="monthlyLoanTrendsChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>

    {{-- Loans Table --}}
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Daftar Pinjaman
                </h3>
            </div>
            <div class="p-4">
                {{ $this->table }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Lazy loading for charts
        let chartsLoaded = false;
        
        function loadCharts() {
            if (chartsLoaded) return;
            
            // Payment Trends Chart
            const paymentTrendsData = @json($this->getPaymentTrends());
            if (paymentTrendsData && document.getElementById('paymentTrendsChart')) {
                // Initialize payment trends chart
                const ctx = document.getElementById('paymentTrendsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: paymentTrendsData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        }
                    }
                });
            }

            // Product Distribution Chart
            const productDistributionData = @json($this->getProductDistribution());
            if (productDistributionData && document.getElementById('productDistributionChart')) {
                const ctx = document.getElementById('productDistributionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: productDistributionData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }

            // Monthly Loan Trends Chart
            const monthlyLoanTrendsData = @json($this->getMonthlyLoanTrends());
            if (monthlyLoanTrendsData && document.getElementById('monthlyLoanTrendsChart')) {
                const ctx = document.getElementById('monthlyLoanTrendsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: monthlyLoanTrendsData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        }
                    }
                });
            }
            
            chartsLoaded = true;
        }

        // Load charts when page is ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(loadCharts, 100);
        });

        // Reload charts on Livewire updates
        document.addEventListener('livewire:updated', function() {
            setTimeout(loadCharts, 100);
        });
    </script>
    @endpush
</x-filament-panels::page>