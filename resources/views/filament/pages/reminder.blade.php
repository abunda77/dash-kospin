<x-filament::page>
    <div class="flex justify-end mb-4">
        <x-filament::button
            wire:click="print"
            icon="heroicon-o-printer"
        >
            Cetak PDF
        </x-filament::button>
    </div>

    {{ $this->table }}
</x-filament::page>
