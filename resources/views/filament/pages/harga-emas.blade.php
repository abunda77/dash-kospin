<x-filament::page>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-medium">Harga Emas Hari Ini</h2>

        </div>

        <!-- Harga Emas Tokopedia -->
        @if($hargaEmasTokopedia)
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-medium">Harga Emas Tokopedia</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded dark:bg-gray-700">
                        <span class="text-gray-600 dark:text-gray-200">Harga Beli:</span>
                        <span class="font-medium text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($hargaEmasTokopedia['buy_price'], 0, ',', '.') }}/gram
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded dark:bg-gray-700">
                        <span class="text-gray-600 dark:text-gray-200">Harga Jual:</span>
                        <span class="font-medium text-green-600 dark:text-green-400">
                            Rp {{ number_format($hargaEmasTokopedia['sell_price'], 0, ',', '.') }}/gram
                        </span>
                    </div>
                </div>
                <div class="flex justify-end items-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <p>Update terakhir: {{ \Carbon\Carbon::parse($hargaEmasTokopedia['date'])->translatedFormat('d F Y') }}</p>
                        <p class="text-xs">Sumber: Tokopedia E-Gold</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tabel Harga Emas -->
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-medium">Harga Emas Antam & UBS</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="px-4 py-2 font-medium text-left text-gray-900 dark:text-gray-100">Kepingan</th>
                            <th class="px-4 py-2 font-medium text-left text-gray-900 dark:text-gray-100">Antam</th>
                            <th class="px-4 py-2 font-medium text-left text-gray-900 dark:text-gray-100">UBS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hargaEmas as $index => $emas)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }} hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-2 border-t dark:border-gray-700">{{ $emas['kepingan'] }}</td>
                                <td class="px-4 py-2 border-t dark:border-gray-700">{{ $emas['antam'] }}</td>
                                <td class="px-4 py-2 border-t dark:border-gray-700">{{ $emas['ubs'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Informasi Pembiayaan -->
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            <h3 class="mb-4 text-lg font-medium">Informasi Pembiayaan</h3>
            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-200">
                <div class="flex items-center">
                    <span class="mr-2 text-lg">•</span>
                    <span>Setoran Awal: <span class="font-medium">5%</span> dari harga emas</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2 text-lg">•</span>
                    <span>Biaya Administrasi: <span class="font-medium">0.5%</span></span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2 text-lg">•</span>
                    <span>Bunga: <span class="font-medium">5%</span> p.a</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2 text-lg">•</span>
                    <span>Tenor: <span class="font-medium">12, 24, 36, 48, 60</span> bulan</span>
                </div>
            </div>
        </div>

        <!-- Catatan Tambahan -->
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            <p>* Jika terjadi macet, bunga setahun dibagi sisa tenor</p>
            <p>* Pelunasan dipercepat tidak dikenakan penalty</p>
        </div>
    </div>
</x-filament::page>
