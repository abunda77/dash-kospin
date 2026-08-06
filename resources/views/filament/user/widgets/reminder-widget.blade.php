<x-filament-widgets::widget>
    @php
        $reminders = $this->getReminders();
    @endphp

    @if(count($reminders) > 0)
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10">
            <header class="flex items-center gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-heroicon-o-bell-alert class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Pengingat & Notifikasi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Informasi yang memerlukan perhatian Anda</p>
                </div>
                <x-filament::badge color="danger" class="ml-auto">{{ count($reminders) }}</x-filament::badge>
            </header>

            <div class="max-h-80 divide-y divide-gray-100 overflow-y-auto dark:divide-white/10">
                @foreach ($reminders as $reminder)
                    <div class="flex items-start gap-3 px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                        <div class="mt-0.5 shrink-0">
                            @if ($reminder['color'] === 'danger')
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400"><x-dynamic-component :component="$reminder['icon']" class="h-4 w-4" /></span>
                            @elseif ($reminder['color'] === 'warning')
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400"><x-dynamic-component :component="$reminder['icon']" class="h-4 w-4" /></span>
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400"><x-dynamic-component :component="$reminder['icon']" class="h-4 w-4" /></span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $reminder['title'] }}</p>
                            <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $reminder['message'] }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ $reminder['date'] }}</span>
                                @if (isset($reminder['aro']) && $reminder['aro'])
                                    <x-filament::badge color="success" size="sm">ARO</x-filament::badge>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-filament-widgets::widget>
