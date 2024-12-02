<div class="flex items-center gap-2">
    <x-filament::button
        size="sm"
        color="danger"
        wire:click="deletePembayaran({{ $getRecord()->id }})"
        icon="heroicon-o-trash"
    >
        Hapus
    </x-filament::button>
</div>
