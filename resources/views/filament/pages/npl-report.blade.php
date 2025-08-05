<x-filament::page>
    {{-- Stats Overview Section --}}
    <div class="grid grid-cols-1 gap-6 mb-8">
        <x-filament::section>
            <div class="grid dark:bg-gray-800 grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach($this->getStatsWidgets() as $widget)
                    {{ $widget }}
                @endforeach
            </div>
        </x-filament::section>
    </div>    {{-- Definition Section --}}
    <x-filament::section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            Non-Performing Loan (NPL)
        </h2>
        <div class="prose max-w-none dark:prose-invert dark:text-gray-200">
            <p>Non-Performing Loan (NPL) adalah kredit bermasalah di mana debitur tidak dapat memenuhi pembayaran angsuran pokok dan/atau bunga selama lebih dari 90 hari.</p>
            <div class="bg-amber-50 dark:bg-amber-900/40 border-l-4 border-amber-400 p-4 my-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            Rasio NPL merupakan indikator penting yang menunjukkan kualitas portofolio kredit koperasi.
                        </p>
                    </div>
                </div>
            </div>
            <p>Kategori kredit bermasalah dalam laporan ini:</p>
            <ul class="dark:text-gray-200">
                <li><strong class="dark:text-white">NPL</strong>: Keterlambatan 90-120 hari</li>
                <li><strong class="dark:text-white">Bermasalah</strong>: Keterlambatan 120-180 hari</li>
                <li><strong class="dark:text-white">Kritis</strong>: Keterlambatan lebih dari 180 hari</li>
            </ul>
        </div>
    </x-filament::section>

    {{-- Data Table Section --}}
    <div class="mt-8">
        {{ $this->table }}
    </div>    {{-- Export Instructions --}}
    <x-filament::section class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            Petunjuk Ekspor Data
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
            Untuk mengekspor data laporan NPL dalam format PDF, klik tombol "Export PDF" di bagian atas halaman.
        </p>
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-300">
            <svg class="h-5 w-5 mr-2 text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
            </svg>
            <span>Laporan yang diekspor mencakup ringkasan statistik dan daftar lengkap pinjaman bermasalah.</span>
        </div>
    </x-filament::section>
</x-filament::page>