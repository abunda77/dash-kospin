<x-filament-panels::page>
    <form wire:submit="cetak">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-printer" class="mt-6">
            Cetak Sertifikat
        </x-filament::button>
    </form>
</x-filament-panels::page>
