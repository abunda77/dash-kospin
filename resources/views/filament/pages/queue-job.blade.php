<x-filament-panels::page>
    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{ $this->table }}
        </div>

        @if($isProcessing || $output)
            <div class="p-4 bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <pre class="text-sm" wire:poll.1s>{{ $output }}</pre>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('queue-output-updated', () => {
                const outputDiv = document.querySelector('pre');
                if (outputDiv) {
                    outputDiv.scrollTop = outputDiv.scrollHeight;
                }
            });
        });
    </script>
</x-filament-panels::page>
