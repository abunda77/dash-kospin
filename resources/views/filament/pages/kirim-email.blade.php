<x-filament-panels::page>
    <div class="w-auto">
        <form wire:submit="submit" class="mb-10 space-y-8">
            <div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
                {{ $this->form }}
            </div>
            <div class="flex justify-center mt-8">
                <x-filament::button
                        type="submit"
                        size="lg"
                        wire:loading.attr="disabled"
                        class="dark:bg-primary-600 dark:hover:bg-primary-500 dark:text-white"
                    >
                        <span wire:loading.remove>
                            Kirim Email
                        </span>
                        <span wire:loading>
                            <svg class="inline w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengirim...
                        </span>
                    </x-filament::button>
                </div>


        </form>

    </div>
</x-filament-panels::page>
