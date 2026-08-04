<div class="min-h-screen flex">
    <!-- Left Side - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-600 via-green-700 to-emerald-800 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>
        <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
            <img src="{{ asset('images/logo_kospin.png') }}" alt="Kospin Sinara Artha" class="w-32 h-32 object-contain mb-8 drop-shadow-lg">
            <h1 class="text-3xl font-bold mb-3 text-center">Koperasi Sinara Artha Naya</h1>
            <p class="text-green-100 text-center max-w-sm leading-relaxed">
                Koperasi Anggota yang amanah, modern, dan terpercaya untuk masa depan finansial Anda.
            </p>
            <div class="mt-12 flex items-center gap-2 text-green-200 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Terdaftar dan diawasi oleh Dinas Koperasi</span>
            </div>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex justify-center mb-8">
                <img src="{{ asset('images/logo_kospin.png') }}" alt="Kospin Sinara Artha" class="w-20 h-20 object-contain">
            </div>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Selamat Datang</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Masuk ke akun anggota Anda</p>
            </div>

            <form wire:submit="login" class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Email
                    </label>
                    <input
                        wire:model="email"
                        id="email"
                        type="email"
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="block w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 @error('email') border-red-400 dark:border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            wire:model="password"
                            id="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                            class="block w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200 @error('password') border-red-400 dark:border-red-500 @enderror"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 dark:bg-gray-800">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 font-medium transition">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3 px-4 rounded-xl bg-green-600 hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-white font-semibold text-sm transition duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <svg wire:loading wire:target="login" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span wire:loading.remove wire:target="login">Masuk</span>
                    <span wire:loading wire:target="login">Memproses...</span>
                </button>
            </form>

            <!-- Register Link -->
            <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum punya akun?
                <a href="{{ route('register') }}" wire:navigate class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 font-semibold transition">
                    Daftar sekarang
                </a>
            </p>

            <!-- Footer -->
            <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800">
                <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                    &copy; {{ date('Y') }} Kospin Sinara Artha. Seluruh hak cipta dilindungi.
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</div>
