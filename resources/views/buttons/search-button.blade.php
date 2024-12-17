<div class="flex items-center gap-3">
    <x-filament::button
        type="submit"
        size="sm"
        icon="heroicon-m-magnifying-glass"
    >
        Cari Data
    </x-filament::button>

    <x-filament::button
        type="button"
        color="danger"
        size="sm"
        icon="heroicon-m-x-mark"
        wire:click="clearSearch"
    >
        Reset
    </x-filament::button>
</div>
