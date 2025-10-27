<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                    </path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">QRIS Generator</h1>
            <p class="text-lg text-gray-600">Ubah QRIS Static menjadi QRIS Dinamis dengan Nominal</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errorMessage)
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Form Section -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Input Data</h2>

                <form wire:submit.prevent="generate" class="space-y-6">
                    <!-- Saved QRIS Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih QRIS Tersimpan (Opsional)
                        </label>
                        <select wire:model="saved_qris"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">-- Pilih QRIS atau input manual --</option>
                            @foreach ($savedQrisList as $qris)
                                <option value="{{ $qris->id }}">{{ $qris->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Static QRIS Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            QRIS Static Code <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="static_qris" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Paste kode QRIS static di sini..."></textarea>
                        @error('static_qris')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Amount Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nominal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" wire:model="amount"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="10000" min="1">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fee Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Biaya
                        </label>
                        <select wire:model="fee_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="Rupiah">Rupiah (Nominal Tetap)</option>
                            <option value="Persentase">Persentase (%)</option>
                        </select>
                    </div>

                    <!-- Fee Value -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nilai Biaya
                        </label>
                        <div class="relative">
                            <input type="number" wire:model="fee_value"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="0" min="0" step="0.01">
                            <span class="absolute right-3 top-2 text-gray-500">
                                {{ $fee_type === 'Persentase' ? '%' : 'Rp' }}
                            </span>
                        </div>
                        @error('fee_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="generateQris"
                            class="flex-1 bg-indigo-600 text-white py-3 px-6 rounded-lg hover:bg-indigo-700 transition duration-200 font-medium shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="generateQris">Generate QRIS</span>
                            <span wire:loading wire:target="generateQris"
                                class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Generating...
                            </span>
                        </button>
                        <button type="button" wire:click="resetForm" wire:loading.attr="disabled"
                            wire:target="generateQris"
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Result Section -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Hasil QRIS Dinamis</h2>

                @if ($dynamicQris)
                    <div class="space-y-6">
                        <!-- Merchant Info -->
                        @if ($merchantName)
                            <div class="bg-indigo-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Merchant:</p>
                                <p class="text-lg font-semibold text-indigo-900">{{ $merchantName }}</p>
                            </div>
                        @endif

                        <!-- QR Code Image -->
                        @if ($qrImageUrl)
                            <div class="flex justify-center bg-gray-50 rounded-lg p-6">
                                <img src="{{ $qrImageUrl }}" alt="QRIS Dynamic QR Code"
                                    class="max-w-full h-auto rounded-lg shadow-md">
                            </div>
                        @endif

                        <!-- QRIS String -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                QRIS Dynamic String:
                            </label>
                            <textarea readonly rows="6"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono" onclick="this.select()">{{ $dynamicQris }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">Klik untuk select semua, lalu copy</p>
                        </div>

                        <!-- Download Button -->
                        @if ($qrImageUrl)
                            <div x-data="{ downloading: false }">
                                <a href="{{ $qrImageUrl }}"
                                    download="qris-dynamic-{{ now()->format('YmdHis') }}.png"
                                    @click="downloading = true; setTimeout(() => downloading = false, 2000)"
                                    :class="downloading ? 'opacity-75 cursor-wait' : ''"
                                    class="block w-full text-center bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition duration-200 font-medium shadow-md hover:shadow-lg">
                                    <span x-show="!downloading">Download QR Code</span>
                                    <span x-show="downloading" class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Downloading...
                                    </span>
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                        <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                            </path>
                        </svg>
                        <p class="text-lg font-medium">Belum ada QRIS yang di-generate</p>
                        <p class="text-sm mt-2">Isi form di sebelah kiri untuk membuat QRIS dinamis</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Cara Penggunaan:</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-600">
                <li>Pilih QRIS tersimpan atau paste kode QRIS static Anda</li>
                <li>Masukkan nominal transaksi yang diinginkan</li>
                <li>Atur biaya tambahan jika diperlukan (opsional)</li>
                <li>Klik "Generate QRIS" untuk membuat QRIS dinamis</li>
                <li>Scan QR code atau copy string QRIS untuk digunakan</li>
            </ol>
        </div>
    </div>
</div>
