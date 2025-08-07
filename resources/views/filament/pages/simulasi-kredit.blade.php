<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow dark:shadow-gray-900">
            {{ $this->form }}
        </div>

        {{-- Table Section --}}
        @if (count($angsuranList) > 0)
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow dark:shadow-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <th class="px-4 py-2 text-left dark:text-gray-200">
                                    @if ($jenisJangkaWaktu === 'bulan')
                                        Bulan Ke
                                    @else
                                        Minggu Ke
                                    @endif
                                </th>
                                <th class="px-4 py-2 text-right dark:text-gray-200">Pokok</th>
                                <th class="px-4 py-2 text-right dark:text-gray-200">Bunga</th>
                                <th class="px-4 py-2 text-right dark:text-gray-200">Total Angsuran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($angsuranList as $angsuran)
                                <tr class="border-t dark:border-gray-700">
                                    <td class="px-4 py-2 dark:text-gray-300">{{ $angsuran['bulan_ke'] }}</td>
                                    <td class="px-4 py-2 text-right dark:text-gray-300">
                                        Rp. {{ number_format($angsuran['pokok'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right dark:text-gray-300">
                                        Rp. {{ number_format($angsuran['bunga'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right dark:text-gray-300">
                                        Rp. {{ number_format($angsuran['angsuran'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t font-medium bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
                                <td class="px-4 py-2 dark:text-gray-200">Total</td>
                                <td class="px-4 py-2 text-right dark:text-gray-200">
                                    Rp. {{ number_format(collect($angsuranList)->sum('pokok'), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-right dark:text-gray-200">
                                    Rp .{{ number_format(collect($angsuranList)->sum('bunga'), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-right dark:text-gray-200">
                                    Rp. {{ number_format(collect($angsuranList)->sum('angsuran'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Summary Section --}}
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow dark:shadow-gray-900">
                <h3 class="text-lg font-medium mb-3 dark:text-gray-200">Ringkasan Simulasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Total Pinjaman</p>
                        <p class="font-medium dark:text-gray-200">
                            Rp {{ number_format((float) str_replace([',', '.'], '', $nominalPinjaman), 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Jangka Waktu</p>
                        <p class="font-medium dark:text-gray-200">
                            {{ $jangkaWaktu }} {{ $jenisJangkaWaktu === 'bulan' ? 'Bulan' : 'Minggu' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Total Bunga</p>
                        <p class="font-medium dark:text-gray-200">
                            Rp {{ number_format((float) collect($angsuranList)->sum('bunga'), 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Total yang Harus Dibayar</p>
                        <p class="font-medium dark:text-gray-200">
                            Rp {{ number_format((float) collect($angsuranList)->sum('angsuran'), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
