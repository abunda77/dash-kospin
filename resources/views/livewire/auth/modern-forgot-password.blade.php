<div class="min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-600 via-green-700 to-emerald-800 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.05&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>
        <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
            <img src="{{ asset('images/logo_kospin.png') }}" alt="Kospin Sinara Artha" class="w-32 h-32 object-contain mb-8 drop-shadow-lg">
            <h1 class="text-3xl font-bold mb-3 text-center">Kospin Sinara Artha</h1>
            <p class="text-green-100 text-center max-w-sm leading-relaxed">
                Pulihkan akses akun Anda dengan aman melalui tautan yang kami kirimkan ke email terdaftar.
            </p>
            <div class="mt-12 flex items-center gap-2 text-green-200 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Proses pemulihan akun yang aman</span>
            </div>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex justify-center mb-8">
                <img src="{{ asset('images/logo_kospin.png') }}" alt="Kospin Sinara Artha" class="w-20 h-20 object-contain">
            </div>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Lupa Password?</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Masukkan email akun Anda untuk menerima tautan reset password</p>
            </div>

            @if ($emailSent)
                <div class="rounded-xl border border-green-200 bg-green-50 p-5 text-center dark:border-green-800 dark:bg-green-950/40">
                    <svg class="mx-auto w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">Periksa email Anda</h3>
                    <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">Tautan reset password telah dikirim ke {{ $email }}.</p>
                </div>
            @else
                <form wire:submit="sendResetLink" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <input wire:model="email" id="email" type="email" autocomplete="email" autofocus placeholder="nama@email.com" class="block w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 @error('email') border-red-400 dark:border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled" class="w-full py-3 px-4 rounded-xl bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-white font-semibold text-sm transition duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg wire:loading wire:target="sendResetLink" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        <span wire:loading.remove wire:target="sendResetLink">Kirim Tautan Reset</span>
                        <span wire:loading wire:target="sendResetLink">Mengirim...</span>
                    </button>
                </form>
            @endif

            <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Ingat password Anda?
                <a href="{{ route('login.modern') }}" wire:navigate class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 font-semibold transition">Kembali ke login</a>
            </p>

            <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800">
                <p class="text-center text-xs text-gray-400 dark:text-gray-500">&copy; {{ date('Y') }} Kospin Sinara Artha. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </div>
</div>
