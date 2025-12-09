<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans text-slate-900">
    
    <!-- Pattern Background (Subtle) -->
    <div class="fixed inset-0 z-0 opacity-40" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="relative z-10 sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo / Icon -->
        <div class="flex justify-center mb-6">
            <div class="h-16 w-16 bg-gradient-to-tr from-blue-600 to-indigo-700 rounded-2xl shadow-lg flex items-center justify-center transform hover:scale-105 transition-transform duration-300">
                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <h2 class="mt-2 text-center text-3xl font-extrabold text-slate-900 tracking-tight">
            KOSPIN Mobile
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600 max-w">
            Akses layanan keuangan dalam genggaman. <br>
            <span class="font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full mt-1 inline-block">Closed Beta Access</span>
        </p>
    </div>

    <div class="relative z-10 mt-8 sm:mx-auto sm:w-full sm:max-w-[480px]">
        @if (!$requestSent)
            <div class="bg-white py-8 px-4 shadow-2xl shadow-slate-200 sm:rounded-2xl sm:px-10 border border-slate-100/50">
                
                <div class="mb-6 text-center">
                    <h3 class="text-lg font-semibold text-slate-900">Permintaan Akses</h3>
                    <p class="text-sm text-slate-500 mt-1">Masukkan email terdaftar untuk verifikasi</p>
                </div>

                <form wire:submit.prevent="submit" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">
                            Email Address
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input 
                                wire:model="email" 
                                id="email" 
                                name="email" 
                                type="email" 
                                autocomplete="email" 
                                required 
                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-slate-300 rounded-lg py-3 transition-colors {{ $loading ? 'bg-slate-50 text-slate-500' : '' }}" 
                                placeholder="nama@kospin.co.id"
                                {{ $loading ? 'disabled' : '' }}
                            >
                        </div>
                        @error('email') 
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }} 
                            </p>
                        @enderror
                    </div>

                    @if ($error)
                        <div class="rounded-lg bg-red-50 p-4 border border-red-100">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Verifikasi Gagal</h3>
                                    <div class="mt-1 text-sm text-red-700">
                                        <p>{{ $error }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Play Store Badge / Button -->
                     <div>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all disabled:opacity-70 disabled:cursor-wait group relative overflow-hidden"
                        >
                            <span wire:loading.remove wire:target="submit">Kirim Permintaan</span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>

                    <!-- Android Badge Visual -->
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-slate-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-slate-500">
                                    Segera hadir di
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-center">
                             <a href="#" class="group flex items-center gap-3 px-5 py-2.5 bg-black text-white rounded-lg hover:bg-slate-800 transition-colors shadow-md">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3.609 1.814L13.792 12 3.61 22.186a.998.998 0 01-.053-.055L3.109 21.6 2.202 3.013l.056-.048.077-.087.051-.06.012-.016.038-.051.056-.08.016-.025.045-.072.012-.016.035-.06.059-.107.039-.084.004-.009A.996.996 0 013.61 1.815zM15.2 13.4l.707.708 3.518 3.518a.99.99 0 001.405 0l.135-.135L15.2 13.4zm.707-2.816L15.2 10.6 20.965 4.83a.99.99 0 00-1.405 0L15.907 8.5 15.9 10.584zM12.383 10.6L4.767 2.984a.5.5 0 00-.773.069L12.383 10.6zm0 2.8l-8.39 8.358a.498.498 0 00.773.07l7.617-8.428z"/>
                                </svg>
                                <div class="flex flex-col items-start leading-none">
                                    <span class="text-[10px] uppercase font-medium text-slate-300">GET IT ON</span>
                                    <span class="text-base font-bold">Google Play</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- Success State -->
            <div class="bg-white py-8 px-4 shadow-2xl shadow-emerald-100 sm:rounded-2xl sm:px-10 border border-emerald-50 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-6">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold text-slate-900 mb-2">Permintaan Terkirim!</h3>
                <p class="text-slate-500 mb-8 max-w-sm mx-auto">{{ $success }}</p>

                @if ($userData)
                    <div class="mt-4 bg-slate-50 rounded-xl p-4 mb-6 border border-slate-100 flex items-center gap-3 text-left">
                         <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold uppercase text-sm">
                            {{ substr($userData['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $userData['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $userData['email'] }}</p>
                        </div>
                    </div>
                @endif

                <!-- Panduan Instalasi -->
                <div class="mt-8 space-y-6 text-left">
                    
                    <!-- APPS 1: SINARA SOON -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase tracking-wide">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                SINARA SOON
                            </h4>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-100 text-indigo-700 font-bold border border-indigo-200">UTAMA</span>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Step 1 Join -->
                            <div>
                                 <p class="text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Langkah 1: Join Beta Program</p>
                                 <a href="https://play.google.com/apps/testing/com.abunda.poskospin" target="_blank" class="group flex items-center justify-between w-full py-2.5 px-3.5 bg-white border border-slate-200 rounded-lg hover:border-indigo-400 hover:shadow-sm transition-all duration-200">
                                    <span class="text-xs text-indigo-600 font-medium truncate pr-2">Link Join Beta Testing</span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                 </a>
                            </div>

                            <!-- Step 2 Download -->
                            <div>
                                 <p class="text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Langkah 2: Download Aplikasi</p>
                                 <a href="https://play.google.com/store/apps/details?id=com.abunda.poskospin" target="_blank" class="group flex items-center justify-between w-full py-2.5 px-3.5 bg-white border border-slate-200 rounded-lg hover:border-indigo-400 hover:shadow-sm transition-all duration-200">
                                    <span class="text-xs text-indigo-600 font-medium truncate pr-2">Download via Play Store</span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                 </a>
                            </div>

                            <!-- Credentials -->
                             <div class="bg-indigo-50/60 rounded-lg p-3.5 border border-indigo-100 mt-2">
                                <p class="text-[11px] font-bold text-indigo-800 mb-2 uppercase flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    Akses Demo Login
                                </p>
                                <div class="grid grid-cols-1 gap-2.5">
                                    <!-- Username -->
                                    <div class="group flex items-center justify-between bg-white px-3 py-2 rounded-md border border-indigo-200 hover:border-indigo-300 transition-colors" x-data="{ copied: false }">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 font-medium uppercase">Username / Email</span>
                                            <span class="text-xs text-slate-700 font-mono font-medium tracking-tight">user3@gmail.com</span>
                                        </div>
                                        <button @click="navigator.clipboard.writeText('user3@gmail.com'); copied=true; setTimeout(() => copied=false, 2000)" class="p-1.5 rounded hover:bg-slate-50 transition-colors focus:outline-none" title="Copy Username">
                                            <svg x-show="!copied" class="w-4 h-4 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            <svg x-show="copied" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    </div>
                                    <!-- Password -->
                                    <div class="group flex items-center justify-between bg-white px-3 py-2 rounded-md border border-indigo-200 hover:border-indigo-300 transition-colors" x-data="{ copied: false }">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 font-medium uppercase">Password</span>
                                            <span class="text-xs text-slate-700 font-mono font-medium tracking-tight">user123</span>
                                        </div>
                                        <button @click="navigator.clipboard.writeText('user123'); copied=true; setTimeout(() => copied=false, 2000)" class="p-1.5 rounded hover:bg-slate-50 transition-colors focus:outline-none" title="Copy Password">
                                            <svg x-show="!copied" class="w-4 h-4 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            <svg x-show="copied" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- APPS 2: SINPOS -->
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 shadow-sm opacity-90 hover:opacity-100 transition-opacity">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-slate-800 uppercase tracking-wide">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                SinPOS App
                            </h4>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-slate-200 text-slate-600 font-bold border border-slate-300">ADDITIONAL</span>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Step 1 Join -->
                            <div>
                                 <p class="text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Langkah 1: Join Beta Program</p>
                                 <a href="https://play.google.com/apps/testing/com.abunda.appkospinpos" target="_blank" class="group flex items-center justify-between w-full py-2.5 px-3.5 bg-white border border-slate-200 rounded-lg hover:border-emerald-400 hover:shadow-sm transition-all duration-200">
                                    <span class="text-xs text-emerald-600 font-medium truncate pr-2">Link Testing SinPOS</span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                 </a>
                            </div>

                            <!-- Step 2 Download -->
                            <div>
                                 <p class="text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Langkah 2: Download Aplikasi</p>
                                 <a href="https://play.google.com/store/apps/details?id=com.abunda.appkospinpos" target="_blank" class="group flex items-center justify-between w-full py-2.5 px-3.5 bg-white border border-slate-200 rounded-lg hover:border-emerald-400 hover:shadow-sm transition-all duration-200">
                                    <span class="text-xs text-emerald-600 font-medium truncate pr-2">Download SinPOS via Play Store</span>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                 </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <button 
                        wire:click="resetForm"
                        class="w-full flex justify-center items-center gap-2 py-2.5 px-4 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Ajukan Permintaan Lain
                    </button>
                </div>
            </div>
        @endif

        <!-- Footer Features -->
        <div class="mt-8 grid grid-cols-3 gap-2 sm:gap-4 text-center">
            <div class="p-2 rounded-lg hover:bg-white/50 transition-colors">
                <div class="mx-auto h-8 w-8 text-indigo-600 mb-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-xs font-medium text-slate-600">Simpanan Aman</p>
            </div>
             <div class="p-2 rounded-lg hover:bg-white/50 transition-colors">
                <div class="mx-auto h-8 w-8 text-indigo-600 mb-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-xs font-medium text-slate-600">Transaksi Cepat</p>
            </div>
             <div class="p-2 rounded-lg hover:bg-white/50 transition-colors">
                <div class="mx-auto h-8 w-8 text-indigo-600 mb-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <p class="text-xs font-medium text-slate-600">Real-time Info</p>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Koperasi Simpan Pinjam Sinara Artha. <br>
            All rights reserved.
        </p>
    </div>
</div>
