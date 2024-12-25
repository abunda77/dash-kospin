<x-filament::page>
    <div class="space-y-4">
        <x-filament::button wire:click="testSpeed" wire:loading.attr="disabled">
            Mulai Speed Test
        </x-filament::button>

        @if($isTestingSpeed)
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
            <div class="text-sm text-gray-600">
                Testing... {{ round($progress) }}%
            </div>
        @endif

        @if(!empty($results))
            <div class="p-4 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-lg font-medium">Hasil Test</h3>
                <div class="space-y-2">
                    <div>
                        <span class="font-medium">Response Time:</span>
                        {{ $results['responseTime'] ?? 0 }} ms
                    </div>
                    <div>
                        <span class="font-medium">Requests per Second:</span>
                        {{ $results['requestsPerSecond'] ?? 0 }} req/s
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::page>
