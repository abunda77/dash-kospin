<x-filament-panels::page>
    <x-filament-panels::form wire:submit="restore">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button
                type="submit"
                color="warning"
                icon="heroicon-o-arrow-path"
            >
                Pulihkan Database
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    <x-filament::section>
        <div class="space-y-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p class="font-medium text-warning-600">⚠️ Peringatan:</p>
                <ul class="mt-2 space-y-1 list-disc list-inside">
                    <li>Proses restore akan menghapus semua data yang ada di database saat ini</li>
                    <li>Pastikan Anda telah membuat backup terbaru sebelum melakukan restore</li>
                    <li>Jangan menutup browser selama proses restore berlangsung</li>
                </ul>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
