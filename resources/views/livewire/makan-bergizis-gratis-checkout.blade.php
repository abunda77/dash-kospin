<div class="min-h-screen bg-gray-100 py-4 px-4">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-1">🍽️ Makan Bergizi Sinara</h1>
            <p class="text-sm text-gray-600">{{ now()->format('d F Y') }}</p>
        </div>

        <!-- Search Form (only show if no hash) -->
        @if (!$hash && !$tabunganData)
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <form wire:submit.prevent="searchTabungan" class="space-y-3">
                    <div>
                        <label for="noTabungan" class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Tabungan
                        </label>
                        <input type="text" id="noTabungan" wire:model="noTabungan"
                            class="w-full px-3 py-2 border-2 border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 text-base"
                            placeholder="Contoh: TAB-001" required autofocus>
                        @error('noTabungan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="searchTabungan">
                            🔍 Cari Data
                        </span>
                        <span wire:loading wire:target="searchTabungan">
                            ⏳ Mencari...
                        </span>
                    </button>
                </form>
            </div>
        @endif

        <!-- Loading State -->
        @if ($loading)
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4 text-center">
                <p class="text-blue-700">⏳ Memuat data...</p>
            </div>
        @endif

        <!-- Error Message -->
        @if ($error)
            <div class="bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p class="text-red-700 text-sm">❌ {{ $error }}</p>
            </div>
        @endif

        <!-- Success Message -->
        @if ($success)
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                <p class="text-green-700 text-sm font-semibold mb-3">✅ {{ $success }}</p>

                @if ($lastCheckoutRecord)
                    <button wire:click="downloadStruk" wire:loading.attr="disabled"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <span wire:loading.remove wire:target="downloadStruk">
                            📄 Download Struk Sekarang
                        </span>
                        <span wire:loading wire:target="downloadStruk">
                            ⏳ Membuat struk...
                        </span>
                    </button>
                @endif
            </div>
        @endif

        <!-- Tabungan Data Display -->
        @if ($tabunganData)
            <div class="grid md:grid-cols-2 gap-3">
                <!-- Kolom Kiri -->
                <div class="space-y-3">
                    <!-- Nasabah Info -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                            👤 Data Nasabah
                        </h2>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-gray-600">Nama Lengkap</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $tabunganData['nasabah']['nama_lengkap'] }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-gray-600">No. Telepon</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $tabunganData['nasabah']['phone'] ?? '-' }}</p>
                                </div>
                                @if ($tabunganData['nasabah']['email'])
                                    <div>
                                        <p class="text-xs text-gray-600">Email</p>
                                        <p class="text-sm font-semibold text-gray-800 truncate">
                                            {{ $tabunganData['nasabah']['email'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Rekening Info -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                            💳 Informasi Rekening
                        </h2>
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-gray-600">No. Tabungan</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $tabunganData['rekening']['no_tabungan'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Status</p>
                                    <span
                                        class="inline-block px-2 py-0.5 text-xs font-semibold rounded {{ $tabunganData['rekening']['status'] === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($tabunganData['rekening']['status']) }}
                                    </span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-xs text-gray-600">Produk</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $tabunganData['rekening']['produk'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Tanggal Buka</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $tabunganData['rekening']['tanggal_buka'] }}</p>
                                </div>
                            </div>
                            @if ($tabunganData['rekening']['usia_rekening'])
                                <div>
                                    <p class="text-xs text-gray-600">Usia Rekening</p>
                                    <p class="text-sm font-semibold text-blue-600">
                                        {{ $tabunganData['rekening']['usia_rekening']['formatted'] }}</p>
                                    <span
                                        class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded {{ $tabunganData['rekening']['usia_rekening']['is_in_contract'] ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $tabunganData['rekening']['usia_rekening']['contract_status'] }}
                                    </span>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-600">Saldo</p>
                                <p class="text-xl font-bold text-green-600">
                                    {{ $tabunganData['rekening']['saldo_formatted'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="space-y-3">
                    <!-- Transaksi Terakhir -->
                    @if ($tabunganData['transaksi_terakhir'])
                        <div class="bg-white rounded-lg shadow p-4">
                            <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                📋 Transaksi Terakhir
                            </h2>
                            <div class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-xs text-gray-600">Kode</p>
                                        <p class="text-sm font-semibold text-gray-800">
                                            {{ $tabunganData['transaksi_terakhir']['kode_transaksi'] }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600">Jenis</p>
                                        <span
                                            class="inline-block px-2 py-0.5 text-xs font-semibold rounded {{ $tabunganData['transaksi_terakhir']['jenis_transaksi'] === 'setoran' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $tabunganData['transaksi_terakhir']['jenis_transaksi_label'] }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Jumlah</p>
                                    <p class="text-lg font-bold text-gray-800">
                                        {{ $tabunganData['transaksi_terakhir']['jumlah_formatted'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Tanggal</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $tabunganData['transaksi_terakhir']['tanggal_transaksi'] }}</p>
                                    @if ($tabunganData['transaksi_terakhir']['mbg_eligibility'])
                                        <div class="mt-2">
                                            <span
                                                class="inline-block px-2 py-1 text-xs font-semibold rounded {{ $tabunganData['transaksi_terakhir']['mbg_eligibility']['eligible'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $tabunganData['transaksi_terakhir']['mbg_eligibility']['status'] }}
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ format_hari_lalu($tabunganData['transaksi_terakhir']['mbg_eligibility']['days_ago']) }}
                                                yang lalu</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Checkout Button -->
                    <div class="bg-white rounded-lg shadow p-4">
                        @if ($alreadyCheckedOut)
                            <div class="text-center py-3">
                                <div class="text-4xl mb-2">⚠️</div>
                                <p class="text-base font-semibold text-gray-800 mb-1">Sudah Checkout Hari Ini</p>
                                <p class="text-sm text-gray-600 mb-3">Nomor tabungan ini sudah melakukan checkout untuk
                                    hari ini.</p>

                                @if ($lastCheckoutRecord)
                                    <!-- Download Struk Button -->
                                    <button wire:click="downloadStruk" wire:loading.attr="disabled"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="downloadStruk">
                                            📄 Download Struk
                                        </span>
                                        <span wire:loading wire:target="downloadStruk">
                                            ⏳ Membuat struk...
                                        </span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <button wire:click="checkout" wire:loading.attr="disabled"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="checkout">
                                    ✅ Checkout Sekarang
                                </span>
                                <span wire:loading wire:target="checkout">
                                    ⏳ Memproses...
                                </span>
                            </button>
                        @endif

                        @if (!$hash)
                            <button wire:click="resetForm"
                                class="w-full mt-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-4 rounded-md transition duration-200">
                                🔍 Cari Nomor Lain
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-500 text-xs">
            <p>&copy; {{ date('Y') }} Koperasi Simpan Pinjam</p>
        </div>
    </div>
</div>
