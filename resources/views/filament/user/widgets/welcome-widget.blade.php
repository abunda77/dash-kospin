<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 dark:from-primary-800 dark:via-primary-900 dark:to-gray-950 p-6 shadow-xl border border-primary-500/20 dark:border-primary-400/10">
        {{-- Enhanced Decorative Elements --}}
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-gradient-to-br from-white/15 to-white/5 dark:from-white/10 dark:to-white/2"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 h-32 w-32 rounded-full bg-gradient-to-tr from-white/10 to-white/2 dark:from-white/5 dark:to-transparent"></div>
        <div class="absolute top-1/2 right-1/4 h-16 w-16 rounded-full bg-gradient-to-br from-white/10 to-transparent opacity-60 dark:from-white/5"></div>
        
        <div class="relative">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-2">
                    <p class="text-primary-100 dark:text-primary-200 text-sm font-medium tracking-wide">
                        {{ $this->getCurrentDate() }}
                    </p>
                    <h2 class="mt-1 text-2xl md:text-3xl font-bold text-white dark:text-gray-100 leading-tight">
                        {{ $this->getGreeting() }}, <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-400 dark:from-yellow-300 dark:to-yellow-500">{{ $this->getUserName() }}</span>! 👋
                    </h2>
                    <p class="mt-2 text-primary-100 dark:text-primary-200 text-sm md:text-base font-medium">
                        Selamat datang di portal anggota Kospin Sinara Artha
                    </p>
                </div>

                @php
                    $profileStatus = $this->getProfileStatus();
                @endphp

                <div class="flex-shrink-0">
                    @if(!$profileStatus['complete'])
                        <div class="bg-gradient-to-r from-amber-500/20 to-amber-600/20 dark:from-amber-400/10 dark:to-amber-500/10 backdrop-blur-sm rounded-xl px-4 py-3 border border-amber-400/30 dark:border-amber-500/20 shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-300 dark:text-amber-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-white dark:text-gray-100 text-sm font-semibold">
                                        {{ $profileStatus['message'] }}
                                    </p>
                                    @if(isset($profileStatus['percentage']))
                                        <div class="mt-2 w-full max-w-xs bg-amber-200/30 dark:bg-amber-400/20 rounded-full h-2.5 shadow-inner">
                                            <div class="bg-gradient-to-r from-amber-400 to-amber-500 dark:from-amber-300 dark:to-amber-400 h-2.5 rounded-full transition-all duration-500 shadow-sm"
                                                 style="width: {{ $profileStatus['percentage'] }}%"></div>
                                        </div>
                                    @endif
                                    <a href="{{ route('filament.user.pages.profile') }}"
                                       class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-200 dark:text-amber-300 hover:text-amber-100 dark:hover:text-amber-200 transition-all duration-200 group">
                                        <span>Lengkapi Sekarang</span>
                                        <x-heroicon-m-arrow-right class="h-3.5 w-3.5 transform group-hover:translate-x-0.5 transition-transform" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gradient-to-r from-emerald-500/20 to-emerald-600/20 dark:from-emerald-400/10 dark:to-emerald-500/10 backdrop-blur-sm rounded-xl px-4 py-3 border border-emerald-400/30 dark:border-emerald-500/20 shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-300 dark:text-emerald-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-white dark:text-gray-100 text-sm font-semibold">
                                        {{ $profileStatus['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions using Filament Button Component --}}
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Tabungan Button --}}
                <x-filament::button
                    href="{{ route('filament.user.pages.tabungan-saya') }}"
                    tag="a"
                    color="success"
                    size="xl"
                    icon="heroicon-s-banknotes"
                    class="flex-col h-auto py-4 gap-2 justify-center"
                    tooltip="Lihat saldo tabungan Anda"
                >
                    <span class="font-bold">Tabungan</span>
                    <span class="text-xs opacity-80">Lihat saldo</span>
                </x-filament::button>

                {{-- Pinjaman Button --}}
                <x-filament::button
                    href="{{ route('filament.user.pages.pinjaman-saya') }}"
                    tag="a"
                    color="danger"
                    size="xl"
                    icon="heroicon-s-credit-card"
                    class="flex-col h-auto py-4 gap-2 justify-center"
                    tooltip="Cek angsuran pinjaman Anda"
                >
                    <span class="font-bold">Pinjaman</span>
                    <span class="text-xs opacity-80">Cek angsuran</span>
                </x-filament::button>

                {{-- Deposito Button --}}
                <x-filament::button
                    href="{{ route('filament.user.pages.deposito-saya') }}"
                    tag="a"
                    color="info"
                    size="xl"
                    icon="heroicon-s-building-library"
                    class="flex-col h-auto py-4 gap-2 justify-center"
                    tooltip="Lihat investasi deposito Anda"
                >
                    <span class="font-bold">Deposito</span>
                    <span class="text-xs opacity-80">Lihat investasi</span>
                </x-filament::button>

                {{-- Profile Button --}}
                <x-filament::button
                    href="{{ route('filament.user.pages.profile') }}"
                    tag="a"
                    color="warning"
                    size="xl"
                    icon="heroicon-s-user-circle"
                    class="flex-col h-auto py-4 gap-2 justify-center"
                    tooltip="Kelola data profil Anda"
                >
                    <span class="font-bold">Profile</span>
                    <span class="text-xs opacity-80">Kelola data</span>
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
