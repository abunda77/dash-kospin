<x-filament::page>
    <div class="space-y-4">
        <x-filament::button wire:click="testSpeed" wire:loading.attr="disabled">
            Mulai Speed Test
        </x-filament::button>

        <div wire:poll.1s wire:loading.remove wire:target="testSpeed">
            @if($isTestingSpeed)
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-blue-600 dark:bg-blue-500 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Testing... {{ round($progress) }}%
                </div>
            @endif
        </div>

        @if(!empty($results))
            <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Hasil Test</h3>
                <div class="space-y-2">
                    <div class="text-gray-700 dark:text-gray-200">
                        <span class="font-medium">Response Time:</span>
                        {{ $results['responseTime'] ?? 0 }} ms
                    </div>
                    <div class="text-gray-700 dark:text-gray-200">
                        <span class="font-medium">Requests per Second:</span>
                        {{ $results['requestsPerSecond'] ?? 0 }} req/s
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
