<x-filament-panels::page>
    @if(auth()->user()->profile)
        {{ $this->profileInfolist }}
    @else
        <div class="flex flex-col items-center justify-center py-12">
            <x-heroicon-o-user-circle class="w-24 h-24 text-gray-400 dark:text-gray-500" />
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                Profile Belum Ada
            </h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Silakan hubungi admin untuk membuat profile Anda.
            </p>
        </div>
    @endif
</x-filament-panels::page>
