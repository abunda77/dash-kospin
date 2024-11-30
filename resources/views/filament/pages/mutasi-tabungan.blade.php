<x-filament-panels::page>
    <form wire:submit="search">
        {{ $this->form }}

        <div class="flex gap-4 mt-4">
            <x-filament::button type="submit">
                Cari Mutasi
            </x-filament::button>

            <x-filament::button color="danger" wire:click="clearSearch" type="button">
                Clear
            </x-filament::button>
        </div>
    </form>

    @if($isSearchSubmitted)
        <div class="mt-4">
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
