<x-filament-widgets::widget>
    @php
        $reminders = $this->getReminders();
    @endphp

    @if(count($reminders) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-bell class="h-5 w-5 text-primary-500" />
                    Pengingat & Notifikasi
                    <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-400">
                        {{ count($reminders) }}
                    </span>
                </h3>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-64 overflow-y-auto">
                @foreach($reminders as $reminder)
                    <div class="px-4 py-3 flex items-start gap-3 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($reminder['color'] === 'danger')
                                <div class="p-1.5 rounded-full bg-danger-100 dark:bg-danger-900/50">
                                    <x-dynamic-component :component="$reminder['icon']" class="h-4 w-4 text-danger-600 dark:text-danger-400" />
                                </div>
                            @elseif($reminder['color'] === 'warning')
                                <div class="p-1.5 rounded-full bg-warning-100 dark:bg-warning-900/50">
                                    <x-dynamic-component :component="$reminder['icon']" class="h-4 w-4 text-warning-600 dark:text-warning-400" />
                                </div>
                            @else
                                <div class="p-1.5 rounded-full bg-info-100 dark:bg-info-900/50">
                                    <x-dynamic-component :component="$reminder['icon']" class="h-4 w-4 text-info-600 dark:text-info-400" />
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $reminder['title'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $reminder['message'] }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $reminder['date'] }}
                                </span>
                                @if(isset($reminder['aro']) && $reminder['aro'])
                                    <span class="inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-400">
                                        <x-heroicon-m-arrow-path class="h-3 w-3" />
                                        ARO
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
