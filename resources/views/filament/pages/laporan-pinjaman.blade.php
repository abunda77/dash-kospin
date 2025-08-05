<x-filament-panels::page>
    <div>
        {{ $this->form }}
    </div>    <div class="mt-6 space-y-6">
        <!-- Optimized Metrics Overview using Filament Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($this->getStatsWidgets() as $stat)
                <div class="fi-stats-overview-stat">
                    {{ $stat }}
                </div>
            @endforeach
        </div>
        
        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Product Distribution Chart -->
            <x-filament::section>
                <x-slot name="heading">Distribusi Produk Pinjaman</x-slot>
                
                @php
                    $productData = $this->getProductDistribution();
                @endphp
                
                <div id="product-distribution-chart" style="height: 300px;"></div>
                
                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const labels = @json($productData['labels']);
                        const data = @json($productData['data']);
                        const amounts = @json($productData['amounts']);
                        
                        const options = {
                            series: data,
                            chart: {
                                type: 'pie',
                                height: 300
                            },
                            labels: labels,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: '100%'
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }],
                            tooltip: {
                                y: {
                                    formatter: function(value, { seriesIndex }) {
                                        return `${value} pinjaman (Rp ${new Intl.NumberFormat('id-ID').format(amounts[seriesIndex])})`;
                                    }
                                }
                            }
                        };
                        
                        if (document.getElementById('product-distribution-chart')) {
                            const chart = new ApexCharts(document.getElementById('product-distribution-chart'), options);
                            chart.render();
                        }
                    });
                </script>
                @endpush
            </x-filament::section>
            
            <!-- Monthly Loan Trends Chart -->
            <x-filament::section>
                <x-slot name="heading">Tren Pinjaman Bulanan</x-slot>
                
                @php
                    $trendData = $this->getMonthlyLoanTrends();
                @endphp
                
                <div id="monthly-loan-trends-chart" style="height: 300px;"></div>
                
                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const months = @json($trendData['months']);
                        const counts = @json($trendData['counts']);
                        const amounts = @json($trendData['amounts']);
                        
                        const options = {
                            series: [{
                                name: 'Jumlah Pinjaman',
                                type: 'column',
                                data: counts
                            }, {
                                name: 'Nilai Pinjaman (dalam juta)',
                                type: 'line',
                                data: amounts.map(val => val / 1000000)
                            }],
                            chart: {
                                height: 300,
                                type: 'line',
                                stacked: false,
                                toolbar: {
                                    show: false
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                width: [1, 4]
                            },
                            xaxis: {
                                categories: months,
                            },
                            yaxis: [
                                {
                                    title: {
                                        text: 'Jumlah Pinjaman',
                                    },
                                },
                                {
                                    opposite: true,
                                    title: {
                                        text: 'Nilai Pinjaman (juta)'
                                    }
                                }
                            ],
                            tooltip: {
                                y: {
                                    formatter: function (value, { seriesIndex }) {
                                        if (seriesIndex === 0) {
                                            return `${value} pinjaman`;
                                        } else {
                                            return `Rp ${new Intl.NumberFormat('id-ID').format(value * 1000000)}`;
                                        }
                                    }
                                }
                            }
                        };
                        
                        if (document.getElementById('monthly-loan-trends-chart')) {
                            const chart = new ApexCharts(document.getElementById('monthly-loan-trends-chart'), options);
                            chart.render();
                        }
                    });
                </script>
                @endpush
            </x-filament::section>
            
            <!-- Payment Trends Chart -->
            <x-filament::section>
                <x-slot name="heading">Tren Pembayaran Angsuran</x-slot>
                
                @php
                    $paymentData = $this->getPaymentTrends();
                @endphp
                
                <div id="payment-trends-chart" style="height: 300px;"></div>
                
                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const months = @json($paymentData['months']);
                        const counts = @json($paymentData['counts']);
                        const amounts = @json($paymentData['amounts']);
                        
                        const options = {
                            series: [{
                                name: 'Jumlah Transaksi',
                                type: 'column',
                                data: counts
                            }, {
                                name: 'Nilai Pembayaran (dalam juta)',
                                type: 'line',
                                data: amounts.map(val => val / 1000000)
                            }],
                            chart: {
                                height: 300,
                                type: 'line',
                                stacked: false,
                                toolbar: {
                                    show: false
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                width: [1, 4]
                            },
                            xaxis: {
                                categories: months,
                            },
                            yaxis: [
                                {
                                    title: {
                                        text: 'Jumlah Transaksi',
                                    },
                                },
                                {
                                    opposite: true,
                                    title: {
                                        text: 'Nilai Pembayaran (juta)'
                                    }
                                }
                            ],
                            tooltip: {
                                y: {
                                    formatter: function (value, { seriesIndex }) {
                                        if (seriesIndex === 0) {
                                            return `${value} transaksi`;
                                        } else {
                                            return `Rp ${new Intl.NumberFormat('id-ID').format(value * 1000000)}`;
                                        }
                                    }
                                }
                            }
                        };
                        
                        if (document.getElementById('payment-trends-chart')) {
                            const chart = new ApexCharts(document.getElementById('payment-trends-chart'), options);
                            chart.render();
                        }
                    });
                </script>
                @endpush
            </x-filament::section>
            
            <!-- Loan Status Chart -->
            <x-filament::section>
                <x-slot name="heading">Status Pinjaman</x-slot>
                
                <div id="loan-status-chart" style="height: 300px;"></div>
                
                @php
                    $dateRange = $this->getDateRange();
                    $loanStatus = App\Models\Pinjaman::query()
                        ->select('status_pinjaman', DB::raw('count(*) as count'))
                        ->when($this->productFilter, fn($query) => $query->where('produk_pinjaman_id', $this->productFilter))
                        ->groupBy('status_pinjaman')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            $status = ucfirst($item->status_pinjaman);
                            return [$status => $item->count];
                        })
                        ->toArray();
                @endphp
                
                @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const statusData = @json($loanStatus);
                        
                        const labels = Object.keys(statusData);
                        const data = Object.values(statusData);
                        
                        const colors = {
                            'Approved': '#10b981',
                            'Pending': '#f59e0b',
                            'Rejected': '#ef4444',
                            'Completed': '#3b82f6'
                        };
                        
                        const chartColors = labels.map(label => colors[label] || '#6b7280');
                        
                        const options = {
                            series: data,
                            chart: {
                                type: 'donut',
                                height: 300
                            },
                            labels: labels,
                            colors: chartColors,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: '100%'
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        };
                        
                        if (document.getElementById('loan-status-chart')) {
                            const chart = new ApexCharts(document.getElementById('loan-status-chart'), options);
                            chart.render();
                        }
                    });
                </script>
                @endpush
            </x-filament::section>
        </div>        <!-- Main Table Section -->
        <div>
            <x-filament::section>
                <x-slot name="heading">Daftar Pinjaman</x-slot>
                
                {{ $this->table }}
            </x-filament::section>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <script>
        // Listen for download-file events from Livewire
        document.addEventListener('livewire:init', function () {
            Livewire.on('download-file', function (data) {
                // Create a temporary link and trigger download
                const link = document.createElement('a');
                link.href = data[0].url;
                link.download = data[0].filename;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
