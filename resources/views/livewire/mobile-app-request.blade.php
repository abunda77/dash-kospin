<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Floating Orbs -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-3xl"></div>
        
        <!-- Grid Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22 width=%2232%22 height=%2232%22 fill=%22none%22 stroke=%22rgba(255,255,255,0.03)%22%3E%3Cpath d=%22M0 .5H31.5V32%22/%3E%3C/svg%3E')] [mask-image:radial-gradient(ellipse_50%_50%_at_50%_50%,black_70%,transparent_100%)]"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 py-8 md:py-16 min-h-screen flex flex-col items-center justify-center">
        <!-- Hero Section -->
        <div class="text-center mb-10 md:mb-16 max-w-3xl mx-auto">
            <!-- Logo/Icon -->
            <div class="inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 shadow-2xl shadow-purple-500/30 mb-6 transform hover:scale-105 transition-all duration-300">
                <svg class="w-10 h-10 md:w-12 md:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-500/30 text-amber-300 text-sm font-medium mb-6 backdrop-blur-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
                Uji Coba Tertutup (Closed Beta)
            </div>

            <!-- Title -->
            <h1 class="text-3xl md:text-5xl lg:text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white via-purple-200 to-white mb-4 md:mb-6 leading-tight">
                Aplikasi Mobile
                <br class="hidden sm:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-indigo-400">KOSPIN Sinara Artha</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-base md:text-lg lg:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Nikmati kemudahan mengelola tabungan & pinjaman langsung dari smartphone Anda. 
                Daftar sekarang untuk mendapatkan akses eksklusif program uji coba.
            </p>
        </div>

        <!-- Main Content -->
        <div class="w-full max-w-md mx-auto">
            @if (!$requestSent)
                <!-- Request Form Card -->
                <div class="relative group">
                    <!-- Glow Effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 rounded-2xl blur-lg opacity-40 group-hover:opacity-60 transition duration-500"></div>
                    
                    <div class="relative bg-slate-800/90 backdrop-blur-xl rounded-2xl border border-slate-700/50 p-6 md:p-8 shadow-2xl">
                        <!-- Form Header -->
                        <div class="text-center mb-6">
                            <h2 class="text-xl md:text-2xl font-semibold text-white mb-2">Daftar Uji Coba</h2>
                            <p class="text-slate-400 text-sm">
                                Masukkan email yang terdaftar di sistem kami
                            </p>
                        </div>

                        <!-- Form -->
                        <form wire:submit.prevent="submit" class="space-y-5">
                            <!-- Email Input -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-slate-300">
                                    Alamat Email
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        wire:model="email"
                                        class="w-full pl-12 pr-4 py-3.5 bg-slate-900/60 border border-slate-600/50 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all duration-200"
                                        placeholder="contoh@email.com"
                                        required
                                        autofocus
                                        {{ $loading ? 'disabled' : '' }}
                                    >
                                </div>
                                @error('email')
                                    <p class="text-red-400 text-sm mt-1 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Error Alert -->
                            @if ($error)
                                <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4">
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-red-400 font-medium text-sm">Email Tidak Ditemukan</h4>
                                            <p class="text-red-300/80 text-sm mt-1">{{ $error }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Submit Button -->
                            <button 
                                type="submit"
                                wire:loading.attr="disabled"
                                class="w-full relative group/btn overflow-hidden rounded-xl py-3.5 px-6 text-white font-semibold transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed"
                                {{ $loading ? 'disabled' : '' }}
                            >
                                <!-- Button Background -->
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-indigo-600 transition-all duration-300 group-hover/btn:from-purple-500 group-hover/btn:to-indigo-500"></div>
                                
                                <!-- Shimmer Effect -->
                                <div class="absolute inset-0 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300">
                                    <div class="absolute inset-0 translate-x-[-100%] group-hover/btn:animate-[shimmer_1.5s_ease-in-out_infinite] bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                                </div>
                                
                                <span class="relative flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="submit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="submit">Kirim Permintaan</span>
                                    <span wire:loading wire:target="submit">Memproses...</span>
                                </span>
                            </button>
                        </form>

                        <!-- Info Note -->
                        <div class="mt-6 pt-6 border-t border-slate-700/50">
                            <div class="flex items-start gap-3 text-slate-400 text-sm">
                                <svg class="w-5 h-5 text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>
                                    Pastikan email Anda sudah terdaftar sebagai anggota KOSPIN Sinara Artha. 
                                    Jika belum, silakan kunjungi kantor kami untuk pendaftaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Success State -->
                <div class="relative group">
                    <!-- Glow Effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 rounded-2xl blur-lg opacity-40"></div>
                    
                    <div class="relative bg-slate-800/90 backdrop-blur-xl rounded-2xl border border-slate-700/50 p-8 shadow-2xl text-center">
                        <!-- Success Icon -->
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/30 mb-6">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        <h2 class="text-2xl font-bold text-white mb-3">Permintaan Terkirim!</h2>
                        
                        <p class="text-slate-400 mb-6 leading-relaxed">
                            {{ $success }}
                        </p>

                        @if ($userData)
                            <div class="bg-slate-900/50 rounded-xl p-4 mb-6 border border-slate-700/50">
                                <div class="flex items-center justify-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-white font-semibold uppercase">
                                        {{ substr($userData['name'], 0, 1) }}
                                    </div>
                                    <div class="text-left">
                                        <p class="text-white font-medium">{{ $userData['name'] }}</p>
                                        <p class="text-slate-400 text-sm">{{ $userData['email'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- What's Next Section -->
                        <div class="bg-slate-900/30 rounded-xl p-5 mb-6 text-left border border-slate-700/30">
                            <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Langkah Selanjutnya
                            </h3>
                            <ul class="space-y-2 text-slate-400 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-400 mt-1">✓</span>
                                    Tim kami akan meninjau permintaan Anda
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-400 mt-1">✓</span>
                                    Anda akan menerima email dengan link download
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-400 mt-1">✓</span>
                                    Proses biasanya memakan waktu 1-2 hari kerja
                                </li>
                            </ul>
                        </div>

                        <!-- Reset Button -->
                        <button 
                            wire:click="resetForm"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white font-medium transition-all duration-200 border border-slate-600/50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Ajukan Permintaan Lain
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Features Section -->
        <div class="w-full max-w-4xl mx-auto mt-12 md:mt-20">
            <h3 class="text-center text-lg md:text-xl font-semibold text-white mb-8 md:mb-10">
                Fitur Unggulan Aplikasi Mobile
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <!-- Feature 1 -->
                <div class="group relative bg-slate-800/40 hover:bg-slate-800/60 backdrop-blur-sm rounded-xl p-5 border border-slate-700/30 hover:border-purple-500/30 transition-all duration-300">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500/20 to-indigo-500/20 text-purple-400 mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Cek Saldo Real-time</h4>
                    <p class="text-slate-400 text-sm">Pantau saldo tabungan dan status pinjaman Anda kapan saja, di mana saja.</p>
                </div>

                <!-- Feature 2 -->
                <div class="group relative bg-slate-800/40 hover:bg-slate-800/60 backdrop-blur-sm rounded-xl p-5 border border-slate-700/30 hover:border-indigo-500/30 transition-all duration-300">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-500/20 to-blue-500/20 text-indigo-400 mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Riwayat Transaksi</h4>
                    <p class="text-slate-400 text-sm">Lihat seluruh riwayat transaksi tabungan dan pembayaran cicilan.</p>
                </div>

                <!-- Feature 3 -->
                <div class="group relative bg-slate-800/40 hover:bg-slate-800/60 backdrop-blur-sm rounded-xl p-5 border border-slate-700/30 hover:border-teal-500/30 transition-all duration-300">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-br from-teal-500/20 to-emerald-500/20 text-teal-400 mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h4 class="text-white font-semibold mb-2">Notifikasi Instan</h4>
                    <p class="text-slate-400 text-sm">Dapatkan notifikasi untuk jatuh tempo, promosi, dan update penting.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 md:mt-16 pb-8">
            <div class="inline-flex items-center gap-2 text-slate-500 text-sm mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Data Anda dilindungi dengan enkripsi</span>
            </div>
            <p class="text-slate-500 text-sm">
                &copy; {{ date('Y') }} Koperasi Simpan Pinjam Sinara Artha
            </p>
        </div>
    </div>

    <style>
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</div>
