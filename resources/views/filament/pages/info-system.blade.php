@php
    $systemInfo = $this->systemInfo();
    $memory = $systemInfo->phpMemoryUsage();
    $composer = $systemInfo->composerPackages();
    $modules = $systemInfo->phpModules();
    $unloaded = $systemInfo->unloadedExtensions();
    $moduleCounts = $systemInfo->moduleCountsByCategory();

    $badgeClasses = [
        'Core' => 'sysinfo-badge sysinfo-badge--core',
        'Zend' => 'sysinfo-badge sysinfo-badge--zend',
        'Ekstensi' => 'sysinfo-badge sysinfo-badge--ext',
        'Belum aktif' => 'sysinfo-badge sysinfo-badge--idle',
        'Production' => 'sysinfo-badge sysinfo-badge--core',
        'Development' => 'sysinfo-badge sysinfo-badge--dev',
    ];
@endphp

<x-filament::page>
    @once
        <style>
            .sysinfo-chip,
            .sysinfo-badge {
                display: inline-flex;
                align-items: center;
                border-radius: 0.5rem;
                padding: 0.125rem 0.5rem;
                font-size: 0.75rem;
                font-weight: 600;
                background-color: #f3f4f6;
                color: #374151;
            }

            .sysinfo-badge {
                border-radius: 0.375rem;
                font-size: 0.6875rem;
            }

            .sysinfo-badge--core {
                background-color: #dbeafe;
                color: #1e40af;
            }

            .sysinfo-badge--zend {
                background-color: #ede9fe;
                color: #5b21b6;
            }

            .sysinfo-badge--ext {
                background-color: #dcfce7;
                color: #166534;
            }

            .sysinfo-badge--idle {
                background-color: #fef3c7;
                color: #92400e;
            }

            .sysinfo-badge--dev {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .dark .sysinfo-chip,
            .dark .sysinfo-badge {
                background-color: rgba(255, 255, 255, 0.1);
                color: #e5e7eb;
            }

            .dark .sysinfo-badge--core {
                background-color: rgba(59, 130, 246, 0.2);
                color: #93c5fd;
            }

            .dark .sysinfo-badge--zend {
                background-color: rgba(139, 92, 246, 0.2);
                color: #c4b5fd;
            }

            .dark .sysinfo-badge--ext {
                background-color: rgba(34, 197, 94, 0.2);
                color: #86efac;
            }

            .dark .sysinfo-badge--idle {
                background-color: rgba(245, 158, 11, 0.2);
                color: #fcd34d;
            }

            .dark .sysinfo-badge--dev {
                background-color: rgba(239, 68, 68, 0.2);
                color: #fca5a5;
            }

            .sysinfo-bar {
                height: 8px;
                border-radius: 9999px;
                background-color: #e5e7eb;
                overflow: hidden;
            }

            .dark .sysinfo-bar {
                background-color: #374151;
            }

            .sysinfo-bar-fill {
                height: 8px;
                border-radius: 9999px;
                background-color: #2563eb;
            }

            [x-cloak] {
                display: none !important;
            }
        </style>
    @endonce

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <x-filament::tabs contained>
                @foreach ($this->tabs() as $key => $label)
                    <x-filament::tabs.item :active="$activeTab === $key" wire:click="setTab('{{ $key }}')">
                        {{ $label }}
                    </x-filament::tabs.item>
                @endforeach
            </x-filament::tabs>

            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <input type="checkbox" wire:model.live="autoRefresh" class="rounded border-gray-300 dark:border-white/10">
                    Auto refresh 30 detik
                </label>

                <x-filament::button wire:click="refresh" size="sm" color="gray" icon="heroicon-o-arrow-path">
                    Refresh
                </x-filament::button>
            </div>
        </div>

        <div @if ($autoRefresh) wire:poll.30s @endif class="space-y-6">
            @if ($activeTab === 'overview')
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($systemInfo->overviewCards() as $card)
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <p class="mt-1 text-lg font-semibold text-gray-950 break-words dark:text-white">{{ $card['value'] }}</p>
                            @if (filled($card['sub']))
                                <p class="mt-1 text-xs text-gray-500 break-words dark:text-gray-400">{{ $card['sub'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <x-filament::section heading="Catatan">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Halaman ini hanya menampilkan nilai konfigurasi terpilih, bukan keluaran mentah
                        <code class="font-mono">phpinfo()</code>, sehingga path sensitif dan variabel environment
                        tidak ikut terbuka. Data perangkat keras di-cache selama satu jam dan bisa diambil ulang
                        dengan tombol Refresh.
                    </p>
                </x-filament::section>
            @endif

            @if ($activeTab === 'os')
                @include('filament.pages.partials.info-rows', [
                    'heading' => 'Detail Sistem Operasi',
                    'rows' => $systemInfo->osInfo(),
                ])
            @endif

            @if ($activeTab === 'php')
                <x-filament::section heading="Pemakaian Memori PHP">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $memory['usage_label'] }} dari {{ $memory['limit_label'] }}</span>
                            <span>{{ $memory['percent_label'] }}</span>
                        </div>

                        <div class="sysinfo-bar">
                            <div class="sysinfo-bar-fill" style="width: {{ $memory['percent'] }}%"></div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Puncak pemakaian: {{ $memory['peak_label'] }}
                        </p>
                    </div>
                </x-filament::section>

                @include('filament.pages.partials.info-rows', [
                    'heading' => 'Konfigurasi PHP',
                    'rows' => $systemInfo->phpInfo(),
                ])
            @endif

            @if ($activeTab === 'modules')
                <x-filament::section heading="Modul PHP Aktif ({{ count($modules) }})">
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach ($moduleCounts as $category => $count)
                            <span class="sysinfo-chip">{{ $category }}: {{ $count }}</span>
                        @endforeach
                    </div>

                    @include('filament.pages.partials.data-table', [
                        'rows' => $modules,
                        'columns' => ['name' => 'Modul', 'version' => 'Versi', 'category' => 'Kategori'],
                        'badgeColumns' => ['category'],
                        'badgeClasses' => $badgeClasses,
                        'perPage' => 15,
                        'placeholder' => 'Cari modul…',
                        'emptyText' => 'Tidak ada modul yang cocok.',
                    ])
                </x-filament::section>

                <x-filament::section heading="Tersedia di Direktori Ekstensi ({{ count($unloaded) }})">
                    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                        Ekstensi berikut ada di <code class="font-mono">{{ ini_get('extension_dir') }}</code>
                        tetapi belum dimuat oleh <code class="font-mono">php.ini</code>.
                    </p>

                    @include('filament.pages.partials.data-table', [
                        'rows' => $unloaded,
                        'columns' => ['name' => 'Modul', 'category' => 'Status'],
                        'badgeColumns' => ['category'],
                        'badgeClasses' => $badgeClasses,
                        'perPage' => 15,
                        'placeholder' => 'Cari modul…',
                        'emptyText' => 'Semua ekstensi yang tersedia sudah aktif, atau direktori ekstensi tidak dapat dibaca.',
                    ])
                </x-filament::section>
            @endif

            @if ($activeTab === 'composer')
                <x-filament::section heading="Ringkasan Composer">
                    <div class="flex flex-wrap gap-2">
                        <span class="sysinfo-chip">Total: {{ $composer['counts']['total'] }} paket</span>
                        <span class="sysinfo-chip">Produksi: {{ $composer['counts']['production'] }}</span>
                        <span class="sysinfo-chip">Development: {{ $composer['counts']['development'] }}</span>
                        @if (filled($composer['php_constraint']))
                            <span class="sysinfo-chip">Requirement PHP: {{ $composer['php_constraint'] }}</span>
                        @endif
                    </div>
                </x-filament::section>

                <x-filament::section heading="Paket Terpasang">
                    @include('filament.pages.partials.data-table', [
                        'rows' => $composer['packages'],
                        'columns' => ['name' => 'Paket', 'version' => 'Versi', 'license' => 'Lisensi', 'type' => 'Tipe'],
                        'badgeColumns' => ['type'],
                        'badgeClasses' => $badgeClasses,
                        'perPage' => 20,
                        'placeholder' => 'Cari paket, lisensi, atau tipe…',
                        'emptyText' => 'composer.lock tidak ditemukan atau tidak berisi paket.',
                    ])
                </x-filament::section>
            @endif

            @if ($activeTab === 'infra')
                @foreach ($systemInfo->infraGroups() as $group)
                    @include('filament.pages.partials.info-rows', [
                        'heading' => $group['heading'],
                        'rows' => $group['rows'],
                    ])
                @endforeach
            @endif
        </div>
    </div>
</x-filament::page>
