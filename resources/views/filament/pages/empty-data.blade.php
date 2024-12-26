<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
        {{-- Card Transaksi Tabungan --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Transaksi Tabungan</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \App\Models\TransaksiTabungan::count() }}
                    </p>
                </div>
                {{ $this->emptyTransaksiTabungan() }}
            </div>
        </x-filament::card>

        {{-- Card Transaksi Pinjaman --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Transaksi Pinjaman</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \App\Models\TransaksiPinjaman::count() }}
                    </p>
                </div>
                {{ $this->emptyTransaksiPinjaman() }}
            </div>
        </x-filament::card>

        {{-- Card Tabungan --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Tabungan</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \App\Models\Tabungan::count() }}
                    </p>
                </div>
                {{ $this->emptyTabungan() }}
            </div>
        </x-filament::card>

        {{-- Card Pinjaman --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Pinjaman</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \App\Models\Pinjaman::count() }}
                    </p>
                </div>
                {{ $this->emptyPinjaman() }}
            </div>
        </x-filament::card>


        {{-- Halaman Filament untuk menampilkan kbutton hapus activities --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Activities</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \App\Models\Activity::count() }}
                    </p>
                </div>
                {{ $this->emptyActivities() }}
            </div>
        </x-filament::card>

        {{-- Card Activities Log --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Activities Log</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \Spatie\Activitylog\Models\Activity::count() }}
                    </p>
                </div>
                {{ $this->emptyActivitiesLog() }}
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Sessions</h2>
                    <p class="text-sm text-gray-600">
                        Total Data: {{ \App\Models\Session::count() }}
                    </p>
                </div>
                {{ $this->emptySessions() }}
            </div>
        </x-filament::card>

        {{-- Card Cache Optimizer --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Cache Optimizer</h2>
                    <p class="text-sm text-gray-600">
                        Membersihkan dan mengoptimasi cache aplikasi
                    </p>
                </div>
                {{ $this->optimizeCache() }}
            </div>
        </x-filament::card>

    </div>
</x-filament-panels::page>
