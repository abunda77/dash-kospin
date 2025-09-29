<x-filament-panels::page>
    <style>
        /* Enhanced dark mode styles for better contrast */
        .dark .bg-white {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }

        .dark .text-gray-900 {
            color: #f9fafb !important;
        }

        .dark .text-gray-500 {
            color: #d1d5db !important;
        }

        .dark .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2) !important;
        }

        /* Custom stat cards for dark mode */
        .dark .stat-card {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%) !important;
            border: 2px solid #4b5563 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
        }

        .dark .stat-card-blue {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
            border-color: #3b82f6 !important;
        }

        .dark .stat-card-green {
            background: linear-gradient(135deg, #14532d 0%, #166534 100%) !important;
            border-color: #10b981 !important;
        }

        .dark .stat-card-amber {
            background: linear-gradient(135deg, #92400e 0%, #b45309 100%) !important;
            border-color: #f59e0b !important;
        }

        .dark .stat-icon {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #f9fafb !important;
        }

        .dark .stat-value {
            color: #f9fafb !important;
            font-weight: 900 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .dark .stat-label {
            color: #e5e7eb !important;
            font-weight: 600 !important;
        }

        .dark .stat-description {
            color: #d1d5db !important;
            font-weight: 500 !important;
        }

        /* Filter section dark mode */
        .dark .filter-title {
            color: #f3f4f6 !important;
            font-weight: 700 !important;
        }

        /* Enhanced contrast for form elements */
        .dark .fi-input {
            background-color: #374151 !important;
            border-color: #4b5563 !important;
            color: #f9fafb !important;
        }

        .dark .fi-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }

        /* Table dark mode enhancements */
        .dark .fi-ta-table {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }

        .dark .fi-ta-header-cell {
            background-color: #111827 !important;
            color: #f9fafb !important;
            font-weight: 700 !important;
        }

        .dark .fi-ta-row {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }

        .dark .fi-ta-row:hover {
            background-color: #374151 !important;
        }

        .dark .fi-ta-cell {
            color: #e5e7eb !important;
        }
    </style>

    <div class="space-y-6">
        <!-- Form Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border dark:border-gray-700">
            <h3 class="filter-title text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Laporan</h3>
            {{ $this->form }}
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($this->getStats() as $index => $stat)
                <div class="stat-card {{ $index === 0 ? 'stat-card-blue' : ($index === 1 ? 'stat-card-green' : 'stat-card-amber') }} bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 border-2 transition-all duration-200 hover:shadow-xl">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon w-12 h-12 rounded-lg bg-{{ $stat['color'] }}-500 dark:bg-opacity-20 flex items-center justify-center">
                                <x-heroicon-o-users class="w-6 h-6 text-white dark:text-gray-100" />
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="stat-label text-sm font-medium text-gray-900 dark:text-gray-100">{{ $stat['label'] }}</h3>
                            <p class="stat-value text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stat['value']) }}</p>
                            <p class="stat-description text-sm text-gray-500 dark:text-gray-300 mt-1">{{ $stat['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border dark:border-gray-700">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>