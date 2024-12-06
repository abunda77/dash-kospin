<x-filament::page>
    <div class="space-y-6">

        {{-- Form Filter --}}
        <div class="p-6 bg-white shadow-sm dark:bg-gray-800 rounded-xl">
            <form wire:submit.prevent="submit" class="space-y-6">
                {{ $this->form }}
            </form>
        </div>

        {{-- Table --}}
        <div class="p-6 bg-white shadow-sm dark:bg-gray-800 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Daftar Jatuh Tempo
                    @switch($periode)
                        @case('bulan-ini')
                            Bulan Ini
                            @break
                        @case('bulan-depan')
                            Bulan Depan
                            @break
                        @case('tahun-depan')
                            Tahun Depan
                            @break
                    @endswitch
                </h2>

                @if($this->getTableQuery()->count() > 0)
                    <x-filament::button
                        wire:click="cetakPDF"
                        icon="heroicon-o-document"
                    >
                        Cetak PDF
                    </x-filament::button>
                @endif
            </div>

            {{ $this->table }}
        </div>
    </div>
</x-filament::page>
