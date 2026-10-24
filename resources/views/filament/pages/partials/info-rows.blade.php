@props([
    'heading' => null,
    'rows' => [],
])

<x-filament::section :heading="$heading">
    <dl class="divide-y divide-gray-100 dark:divide-white/10">
        @foreach ($rows as $row)
            <div class="grid gap-1 py-2 md:grid-cols-3">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $row['label'] }}
                </dt>
                <dd class="font-mono text-sm text-gray-950 dark:text-white break-words md:col-span-2">
                    {{ $row['value'] }}
                </dd>
            </div>
        @endforeach
    </dl>
</x-filament::section>
