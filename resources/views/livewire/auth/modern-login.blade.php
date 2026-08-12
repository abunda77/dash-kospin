<div class="relative min-h-screen overflow-hidden">
    <div class="auth-glow pointer-events-none fixed -right-32 -top-32 h-96 w-96 rounded-full bg-green-500/[.09] blur-3xl"></div>
    <div class="auth-glow pointer-events-none fixed -bottom-40 -left-40 h-96 w-96 rounded-full bg-yellow-400/[.05] blur-3xl" style="animation-delay: -6s"></div>

    <header class="sticky top-0 z-30 border-b border-white/[.07] bg-[#0a0f0c]/80 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-3.5 sm:px-8" aria-label="Navigasi utama">
            <a href="{{ url('/') }}" wire:navigate class="group flex items-center gap-3" aria-label="Kembali ke halaman utama">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white p-1.5 shadow-lg shadow-black/20 transition duration-300 group-hover:shadow-green-500/20">
                    <img src="{{ asset('images/logo_kospin.png') }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="hidden leading-tight sm:block">
                    <span class="auth-brand-font block text-sm font-bold tracking-tight text-white">SINARA ARTHA NAYA</span>
                    <span class="block text-[10px] font-medium text-[#a3c9b0]">Koperasi simpanan anggota</span>
                </span>
            </a>
            <a href="{{ url('/') }}" wire:navigate class="group inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/[.03] px-4 py-2.5 text-sm font-semibold text-[#a3c9b0] transition duration-300 hover:border-green-400/30 hover:bg-white/[.06] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Beranda
            </a>
        </nav>
    </header>

    <main class="relative z-10 mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl lg:grid-cols-[.94fr_1.06fr]">
        <!-- visual panel -->
        <section class="auth-visual relative hidden overflow-hidden border-r border-white/[.07] p-12 lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(34,197,94,.16),transparent_35%)]"></div>

            <div class="relative">
                <span class="mono-font inline-flex items-center gap-2 rounded-full border border-yellow-400/25 bg-yellow-400/[.08] px-3 py-2 text-[10px] font-semibold uppercase tracking-[.16em] text-yellow-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-300"></span>
                    Portal Anggota
                </span>
                <h1 class="auth-brand-font mt-7 max-w-md text-4xl font-bold leading-tight tracking-[-.03em] text-white">
                    Kelola simpanan dengan <span class="text-green-400">aman dan terencana.</span>
                </h1>
                <p class="mt-5 max-w-md text-base leading-7 text-[#a3c9b0]">
                    Satu akses untuk layanan koperasi yang jelas, mudah digunakan, dan terpercaya.
                </p>
            </div>

            <!-- passbook minicard -->
            <div class="relative rounded-2xl border border-white/[.08] bg-[#111a14]/85 p-6 backdrop-blur-xl">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <span class="mono-font text-[10px] font-medium uppercase tracking-[.16em] text-[#a3c9b0]">Saldo anggota</span>
                        <div class="mono-font mt-1 text-xl font-semibold tracking-tight text-white">Tumbuh <span class="text-green-400">stabil</span></div>
                    </div>
                    <span class="mono-font inline-flex items-center gap-1.5 text-xs font-semibold text-green-400" aria-label="tren positif">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Terus naik
                    </span>
                </div>

                <svg class="mt-4 h-16 w-full" viewBox="0 0 600 120" preserveAspectRatio="none" role="img" aria-label="Grafik pertumbuhan simpanan">
                    <defs>
                        <linearGradient id="authSparkFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#22c55e" stop-opacity="0.28"/>
                            <stop offset="100%" stop-color="#22c55e" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path class="sparkline-fill" d="M0,98 L88,86 L176,92 L264,66 L352,76 L440,44 L528,30 L600,20 L600,120 L0,120 Z" fill="url(#authSparkFill)"/>
                    <path class="sparkline-path" d="M0,98 C40,92 60,88 88,86 C120,83 148,94 176,92 C210,89 230,72 264,66 C300,60 322,82 352,76 C384,71 410,56 440,44 C472,33 500,34 528,30 C558,27 580,23 600,20"
                          fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" pathLength="1"/>
                    <circle class="sparkline-dot" cx="600" cy="20" r="5" fill="#22c55e" stroke="#0a0f0c" stroke-width="3"/>
                </svg>

                <div class="mt-5 grid grid-cols-3 gap-4 border-t border-white/[.08] pt-5">
                    <div>
                        <p class="font-bold text-white">Aman</p>
                        <p class="mt-1 text-xs text-[#73937d]">Data terlindungi</p>
                    </div>
                    <div class="border-x border-white/[.08] px-4">
                        <p class="font-bold text-white">Mudah</p>
                        <p class="mt-1 text-xs text-[#73937d]">Akses sederhana</p>
                    </div>
                    <div>
                        <p class="font-bold text-white">Terbuka</p>
                        <p class="mt-1 text-xs text-[#73937d]">Informasi jelas</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- form panel -->
        <section class="flex items-center justify-center px-5 py-10 sm:px-10 lg:px-16 lg:py-16">
            <div class="auth-reveal w-full max-w-md" style="animation-delay: .12s">
                <div class="mb-8 lg:hidden">
                    <span class="mono-font inline-flex items-center gap-2 text-xs font-semibold text-green-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                        Portal Anggota
                    </span>
                </div>

                <div class="mb-9">
                    <span class="mono-font text-[10px] font-semibold uppercase tracking-[.2em] text-yellow-300">Selamat datang kembali</span>
                    <h2 class="auth-brand-font mt-3 text-3xl font-bold tracking-tight text-white">Masuk ke akun Anda</h2>
                    <p class="mt-3 text-sm leading-6 text-[#8cb399]">Gunakan email dan password yang terdaftar untuk melanjutkan.</p>
                </div>

                <form wire:submit="login" class="space-y-5" novalidate>
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-[#cce6d4]">Email</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#6f947a]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            <input wire:model="email" id="email" type="email" autocomplete="email" autofocus placeholder="nama@email.com" aria-invalid="@error('email') true @else false @enderror" aria-describedby="email-error" class="block w-full rounded-xl border bg-[#0d1510] py-3.5 pl-11 pr-4 text-sm text-white outline-none transition duration-200 placeholder:text-[#577060] focus:border-green-400 focus:ring-4 focus:ring-green-500/10 @error('email') border-red-400 @else border-white/10 @enderror">
                        </div>
                        @error('email')
                            <p id="email-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-[#cce6d4]">Password</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#6f947a]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <input wire:model="password" id="password" type="password" autocomplete="current-password" placeholder="Masukkan password" aria-invalid="@error('password') true @else false @enderror" aria-describedby="password-error" class="block w-full rounded-xl border bg-[#0d1510] py-3.5 pl-11 pr-12 text-sm text-white outline-none transition duration-200 placeholder:text-[#577060] focus:border-green-400 focus:ring-4 focus:ring-green-500/10 @error('password') border-red-400 @else border-white/10 @enderror">
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-[#6f947a] transition hover:bg-white/[.05] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-400" aria-label="Tampilkan atau sembunyikan password">
                                <svg id="eye-open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eye-closed" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div wire:show="showCaptcha" class="space-y-3 rounded-xl border border-white/10 bg-[#0d1510] p-4">
                            <div class="flex items-center justify-between gap-3">
                                <label for="captcha-answer" class="text-sm font-semibold text-[#cce6d4]">Verifikasi keamanan</label>
                                <button type="button" wire:click="refreshCaptcha" class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-400 transition hover:text-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-400" aria-label="Muat ulang soal">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Ganti soal
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="mono-font rounded-lg bg-white/[.06] px-4 py-3 text-lg font-semibold tracking-wider text-white select-none">{{ $captchaQuestion }} = ?</span>
                                <input wire:model="captchaAnswer" id="captcha-answer" type="text" inputmode="numeric" autocomplete="off" placeholder="Jawaban" aria-label="Jawaban keamanan" class="block w-full rounded-xl border bg-[#0a0f0c] px-4 py-3.5 text-sm text-white outline-none transition duration-200 placeholder:text-[#577060] focus:border-green-400 focus:ring-4 focus:ring-green-500/10 @error('captchaAnswer') border-red-400 @else border-white/10 @enderror">
                            </div>
                            @error('captchaAnswer')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    <div class="flex items-center justify-between gap-4 pt-1">
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-[#8cb399]">
                            <input wire:model="remember" type="checkbox" class="h-4 w-4 rounded border-white/20 bg-[#0d1510] text-green-500 focus:ring-green-500 focus:ring-offset-[#0a0f0c]">
                            Ingat saya
                        </label>
                        <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-semibold text-green-400 transition hover:text-green-300">Lupa password?</a>
                    </div>

                    <button type="submit" wire:loading.attr="disabled" class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg wire:loading wire:target="login" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        <span wire:loading.remove wire:target="login">Masuk ke akun</span>
                        <span wire:loading wire:target="login">Memproses...</span>
                    </button>
                </form>

                <p class="mt-7 text-center text-sm text-[#789b83]">
                    Belum punya akun?
                    <a href="{{ route('register') }}" wire:navigate class="font-bold text-green-400 transition hover:text-green-300">Daftar sekarang</a>
                </p>

                <p class="mt-8 border-t border-white/[.07] pt-6 text-center text-xs text-[#55705e]">&copy; {{ date('Y') }} Koperasi Sinara Artha Naya</p>
            </div>
        </section>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            input.type = input.type === 'password' ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden');
            eyeClosed.classList.toggle('hidden');
        }
    </script>
</div>