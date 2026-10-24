@props([
    'rows' => [],
    'columns' => [],
    'badgeColumns' => [],
    'badgeClasses' => [],
    'perPage' => 15,
    'placeholder' => 'Cari…',
    'emptyText' => 'Tidak ada data.',
])

@php
    $firstColumn = (string) (array_key_first($columns) ?? 'name');
@endphp

<div
    x-data="{
        search: '',
        sortKey: '{{ $firstColumn }}',
        sortAsc: true,
        page: 1,
        perPage: {{ (int) $perPage }},
        rows: {{ \Illuminate\Support\Js::from(array_values($rows)) }},
        badgeClasses: {{ \Illuminate\Support\Js::from((object) $badgeClasses) }},
        get filtered() {
            const query = this.search.toLowerCase().trim();

            const result = this.rows.filter((row) => ! query || Object.values(row).join(' ').toLowerCase().includes(query));

            result.sort((left, right) => {
                const a = String(left[this.sortKey] ?? '').toLowerCase();
                const b = String(right[this.sortKey] ?? '').toLowerCase();

                if (a === b) {
                    return 0;
                }

                return (a < b ? -1 : 1) * (this.sortAsc ? 1 : -1);
            });

            return result;
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },
        get currentPage() {
            return Math.min(this.page, this.totalPages);
        },
        get paged() {
            const start = (this.currentPage - 1) * this.perPage;

            return this.filtered.slice(start, start + this.perPage);
        },
        get rangeFrom() {
            return this.filtered.length === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
        },
        get rangeTo() {
            return Math.min(this.currentPage * this.perPage, this.filtered.length);
        },
        sortBy(column) {
            if (this.sortKey === column) {
                this.sortAsc = ! this.sortAsc;
            } else {
                this.sortKey = column;
                this.sortAsc = true;
            }

            this.page = 1;
        },
        badgeClass(value) {
            return this.badgeClasses[value] ?? 'sysinfo-badge';
        },
    }"
    class="space-y-3"
>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <input
            type="search"
            x-model.debounce.200ms="search"
            x-on:input="page = 1"
            placeholder="{{ $placeholder }}"
            class="w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 dark:border-white/10 dark:bg-white/5 dark:text-white"
        />

        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <span x-text="`${rangeFrom}–${rangeTo} dari ${filtered.length}`"></span>

            <x-filament::button size="xs" color="gray" x-on:click="page = Math.max(1, page - 1)" x-bind:disabled="currentPage === 1">
                Sebelumnya
            </x-filament::button>

            <x-filament::button size="xs" color="gray" x-on:click="page = Math.min(totalPages, page + 1)" x-bind:disabled="currentPage >= totalPages">
                Berikutnya
            </x-filament::button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <table class="w-full text-left text-sm text-gray-950 dark:text-white">
            <thead class="border-b border-gray-200 dark:border-white/10">
                <tr>
                    @foreach ($columns as $key => $label)
                        <th scope="col" class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <button type="button" class="inline-flex items-center gap-1 cursor-pointer select-none" x-on:click="sortBy('{{ $key }}')">
                                {{ $label }}
                                <span class="text-xs" x-show="sortKey === '{{ $key }}'" x-text="sortAsc ? '▲' : '▼'"></span>
                            </button>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                <template x-for="(row, index) in paged" :key="index">
                    <tr>
                        @foreach ($columns as $key => $label)
                            <td class="px-3 py-2 align-top">
                                @if (in_array($key, $badgeColumns, true))
                                    <span x-text="row['{{ $key }}']" :class="badgeClass(row['{{ $key }}'])"></span>
                                @else
                                    <span class="font-mono break-words" x-text="row['{{ $key }}']"></span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </template>
            </tbody>
        </table>

        <p x-show="filtered.length === 0" x-cloak class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ $emptyText }}
        </p>
    </div>
</div>
