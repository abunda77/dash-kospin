<div class="flex gap-2 items-center">
    @if($getRecord()->status_pembayaran === 'PENDING')
        <x-filament::button
            size="sm"
            color="success"
            wire:click="updateStatusLunas({{ $getRecord()->id }})"
            icon="heroicon-o-check-circle"
        >
            LUNASKAN
        </x-filament::button>
    @endif
    <x-filament::button
        size="sm"
        color="danger"
        wire:click="deletePembayaran({{ $getRecord()->id }})"
        icon="heroicon-o-trash"
    >
        Hapus
    </x-filament::button>
</div>
