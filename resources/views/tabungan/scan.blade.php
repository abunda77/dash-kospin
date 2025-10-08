<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tabungan - {{ $tabungan->no_tabungan }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-2xl">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">KoSPIN Dash</h1>
                <p class="text-gray-600">Detail Rekening Tabungan</p>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                Informasi Rekening
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">No. Rekening</label>
                        <p class="text-lg font-semibold text-gray-800">{{ $tabungan->no_tabungan }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nama Nasabah</label>
                        <p class="text-lg font-semibold text-gray-800">
                            {{ $tabungan->profile->first_name }} {{ $tabungan->profile->last_name }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Produk Tabungan</label>
                        <p class="text-lg text-gray-800">{{ $tabungan->produkTabungan->nama_produk }}</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Saldo</label>
                        <p class="text-2xl font-bold text-green-600">{{ format_rupiah($tabungan->saldo) }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Tanggal Buka</label>
                        <p class="text-lg text-gray-800">
                            {{ \Carbon\Carbon::parse($tabungan->tanggal_buka_rekening)->format('d/m/Y') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($tabungan->status_rekening === 'aktif') bg-green-100 text-green-800
                            @elseif($tabungan->status_rekening === 'tidak_aktif') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $tabungan->status_rekening)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        @if($tabungan->profile->phone || $tabungan->profile->email)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                Informasi Kontak
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($tabungan->profile->phone)
                <div>
                    <label class="block text-sm font-medium text-gray-600">No. Telepon</label>
                    <p class="text-lg text-gray-800">{{ $tabungan->profile->phone }}</p>
                </div>
                @endif
                
                @if($tabungan->profile->email)
                <div>
                    <label class="block text-sm font-medium text-gray-600">Email</label>
                    <p class="text-lg text-gray-800">{{ $tabungan->profile->email }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-600 text-sm">
                Data diakses pada: {{ date('d/m/Y H:i:s') }}
            </p>
            <p class="text-gray-500 text-xs mt-2">
                KoSPIN Dash - Sistem Manajemen Koperasi Simpan Pinjam
            </p>
        </div>
    </div>
</body>
</html>