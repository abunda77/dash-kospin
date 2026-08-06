<x-filament-widgets::widget>
    @php
        $profileStatus = $this->getProfileStatus();
    @endphp

    <section class="relative overflow-hidden rounded-2xl border border-primary-200 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 p-5 shadow-sm sm:p-7 dark:border-primary-500/30 dark:from-primary-800 dark:via-primary-900 dark:to-gray-950">
        <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 h-56 w-56 rounded-full bg-primary-400/20 blur-2xl"></div>

        <div class="relative grid gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-start">
            <div class="max-w-2xl">
                <p class="text-sm font-medium text-primary-100">{{ $this->getCurrentDate() }}</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    {{ $this->getGreeting() }}, {{ $this->getUserName() }}
                </h2>
                <p class="mt-2 text-sm leading-6 text-primary-100 sm:text-base">
                    Pantau simpanan, pinjaman, dan deposito Anda dari satu tempat.
                </p>
            </div>

            <div class="w-full xl:w-80">
                @if (! $profileStatus['complete'])
                    <div class="rounded-xl border border-amber-300/30 bg-amber-950/20 p-4 backdrop-blur-sm">
                        <div class="flex gap-3">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-300" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-white">{{ $profileStatus['message'] }}</p>
                                @if (isset($profileStatus['percentage']))
                                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/20">
                                        <div class="h-full rounded-full bg-amber-300" style="width: {{ $profileStatus['percentage'] }}%"></div>
                                    </div>
                                @endif
                                <a href="{{ route('filament.user.pages.profile') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-200 transition hover:text-white">
                                    Lengkapi profil
                                    <x-heroicon-m-arrow-right class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-300/30 bg-emerald-950/20 p-4 backdrop-blur-sm">
                        <x-heroicon-o-check-circle class="h-5 w-5 shrink-0 text-emerald-300" />
                        <p class="text-sm font-semibold text-white">{{ $profileStatus['message'] }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="relative mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::button href="{{ route('filament.user.pages.tabungan-saya') }}" tag="a" color="gray" icon="heroicon-s-banknotes" class="!justify-start !rounded-xl !border-white/10 !bg-white/10 !px-4 !py-3.5 !text-white shadow-none hover:!bg-white/20 dark:!bg-white/10">
                <span class="text-left"><span class="block text-sm font-semibold">Tabungan</span><span class="block text-xs font-normal text-primary-100">Lihat saldo</span></span>
            </x-filament::button>

            <x-filament::button href="{{ route('filament.user.pages.pinjaman-saya') }}" tag="a" color="gray" icon="heroicon-s-credit-card" class="!justify-start !rounded-xl !border-white/10 !bg-white/10 !px-4 !py-3.5 !text-white shadow-none hover:!bg-white/20 dark:!bg-white/10">
                <span class="text-left"><span class="block text-sm font-semibold">Pinjaman</span><span class="block text-xs font-normal text-primary-100">Cek angsuran</span></span>
            </x-filament::button>

            <x-filament::button href="{{ route('filament.user.pages.deposito-saya') }}" tag="a" color="gray" icon="heroicon-s-building-library" class="!justify-start !rounded-xl !border-white/10 !bg-white/10 !px-4 !py-3.5 !text-white shadow-none hover:!bg-white/20 dark:!bg-white/10">
                <span class="text-left"><span class="block text-sm font-semibold">Deposito</span><span class="block text-xs font-normal text-primary-100">Lihat investasi</span></span>
            </x-filament::button>

            <x-filament::button href="{{ route('filament.user.pages.profile') }}" tag="a" color="gray" icon="heroicon-s-user-circle" class="!justify-start !rounded-xl !border-white/10 !bg-white/10 !px-4 !py-3.5 !text-white shadow-none hover:!bg-white/20 dark:!bg-white/10">
                <span class="text-left"><span class="block text-sm font-semibold">Profil</span><span class="block text-xs font-normal text-primary-100">Kelola data</span></span>
            </x-filament::button>
        </div>
    </section>
</x-filament-widgets::widget>
