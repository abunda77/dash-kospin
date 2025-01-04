<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Info --}}
        <div class="p-4 bg-warning-50 border border-warning-300 rounded-lg">
            <div class="flex items-center gap-x-3">
                <x-heroicon-o-exclamation-circle class="w-6 h-6 text-warning-500"/>
                <div class="text-sm text-warning-700">
                    Halaman ini menampilkan daftar deposito yang telah jatuh tempo dan siap untuk dicairkan.
                </div>
            </div>
        </div>

        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
