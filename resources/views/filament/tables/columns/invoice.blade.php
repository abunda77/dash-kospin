<div class="flex items-center gap-2">
    <x-filament::button
        size="sm"
        color="success"
        wire:click="cetakInvoice({{ $getRecord()->id }})"
        icon="heroicon-o-trash"
    >
        Cetak Invoice
    </x-filament::button>
</div>
