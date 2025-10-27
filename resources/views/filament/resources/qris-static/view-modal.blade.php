<div class="space-y-4">
    {{-- Merchant Info --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Name</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $record->name }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Merchant</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $record->merchant_name ?? '-' }}
                </div>
            </div>
        </div>
        @if ($record->description)
            <div class="mt-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">Description</div>
                <div class="text-sm text-gray-700 dark:text-gray-300">{{ $record->description }}</div>
            </div>
        @endif
    </div>

    {{-- QR Image --}}
    @if ($record->qris_image)
        <div
            class="flex justify-center p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <img src="{{ asset('storage/' . $record->qris_image) }}" alt="QRIS Image"
                class="max-w-xs rounded-lg shadow-md">
        </div>
    @endif

    {{-- QRIS String --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            QRIS String
        </label>
        <div class="relative">
            <textarea readonly rows="4"
                class="w-full px-3 py-2 text-xs font-mono bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white"
                id="qris-string-view">{{ $record->qris_string }}</textarea>
            <button type="button" onclick="copyQrisString()"
                class="absolute top-2 right-2 px-2 py-1 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded transition-colors">
                Copy
            </button>
        </div>
    </div>

    {{-- Status --}}
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
        @if ($record->is_active)
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                Active
            </span>
        @else
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                Inactive
            </span>
        @endif
    </div>

    {{-- Timestamps --}}
    <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 dark:text-gray-400">
        <div>
            <span class="font-medium">Created:</span> {{ $record->created_at->format('d M Y H:i') }}
        </div>
        <div>
            <span class="font-medium">Updated:</span> {{ $record->updated_at->format('d M Y H:i') }}
        </div>
    </div>
</div>

<script>
    function copyQrisString() {
        const textarea = document.getElementById('qris-string-view');
        textarea.select();
        document.execCommand('copy');

        // Simple alert since we're in a modal
        alert('QRIS string copied to clipboard!');
    }
</script>
