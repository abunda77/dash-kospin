<div class="relative min-h-screen overflow-hidden">
    <div class="auth-glow pointer-events-none fixed -right-32 -top-32 h-96 w-96 rounded-full bg-green-500/[.09] blur-3xl"></div>
    <div class="auth-glow pointer-events-none fixed -bottom-40 -left-40 h-96 w-96 rounded-full bg-yellow-400/[.05] blur-3xl" style="animation-delay: -6s"></div>

    <header class="relative z-20 border-b border-white/[.07] bg-[#0a0f0c]/80 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Kembali ke halaman utama">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white p-1.5"><img src="{{ asset('images/logo_kospin.png') }}" alt="" class="h-full w-full object-contain"></span>
                <span class="auth-brand-font hidden text-sm font-bold tracking-tight text-white sm:block">SINARA ARTHA NAYA</span>
            </a>
            <a href="{{ url('/') }}" class="group inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/[.03] px-4 py-2.5 text-sm font-semibold text-[#a3c9b0] transition hover:border-green-400/30 hover:bg-white/[.06] hover:text-white">
                <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Beranda
            </a>
        </nav>
    </header>

    <main class="relative z-10 mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl lg:grid-cols-[.94fr_1.06fr]">
        <section class="auth-visual relative hidden overflow-hidden border-r border-white/[.07] p-12 lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(34,197,94,.16),transparent_35%)]"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 rounded-full border border-yellow-400/25 bg-yellow-400/[.08] px-3 py-2 text-[10px] font-bold uppercase tracking-[.16em] text-yellow-300"><span class="h-1.5 w-1.5 rounded-full bg-yellow-300"></span>Pemulihan Aman</span>
                <h1 class="auth-brand-font mt-7 max-w-md text-4xl font-bold leading-tight tracking-[-.035em] text-white">Pulihkan akses akun dengan <span class="text-green-400">tenang dan aman.</span></h1>
                <p class="mt-5 max-w-md text-base leading-7 text-[#a3c9b0]">Kami akan mengirimkan tautan pemulihan ke alamat email yang terdaftar.</p>
            </div>
            <div class="relative rounded-2xl border border-white/[.08] bg-[#111a14]/85 p-6 backdrop-blur-xl">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-500/15 text-green-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <div><p class="font-bold text-white">Proses terlindungi</p><p class="mt-2 text-sm leading-6 text-[#8cb399]">Tautan reset hanya dikirim ke email akun yang terverifikasi.</p></div>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center px-5 py-10 sm:px-10 lg:px-16">
            <div class="auth-reveal w-full max-w-md" style="animation-delay: .12s">
                <div class="mb-8">
                    <span class="text-[10px] font-bold uppercase tracking-[.18em] text-yellow-300">Pemulihan akun</span>
                    <h2 class="auth-brand-font mt-3 text-3xl font-bold tracking-tight text-white">Lupa password?</h2>
                    <p class="mt-3 text-sm leading-6 text-[#8cb399]">Masukkan email akun Anda untuk menerima tautan reset password.</p>
                </div>

                @if ($emailSent)
                    <div class="rounded-2xl border border-green-400/20 bg-green-500/[.08] p-6 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-green-500 text-[#08110b]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h3 class="auth-brand-font mt-4 font-bold text-white">Periksa email Anda</h3>
                        <p class="mt-2 text-sm leading-6 text-[#a3c9b0]">Tautan reset password telah dikirim ke {{ $email }}.</p>
                    </div>
                @else
                    <form wire:submit="sendResetLink" class="space-y-5">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-[#cce6d4]">Email</label>
                            <input wire:model="email" id="email" type="email" autocomplete="email" autofocus placeholder="nama@email.com" class="block w-full rounded-xl border bg-[#0d1510] px-4 py-3.5 text-sm text-white outline-none transition placeholder:text-[#577060] focus:border-green-400 focus:ring-4 focus:ring-green-500/10 @error('email') border-red-400 @else border-white/10 @enderror">
                            @error('email')<p class="mt-2 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 px-4 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg wire:loading wire:target="sendResetLink" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            <span wire:loading.remove wire:target="sendResetLink">Kirim tautan reset</span>
                            <span wire:loading wire:target="sendResetLink">Mengirim...</span>
                        </button>
                    </form>
                @endif

                <p class="mt-7 text-center text-sm text-[#789b83]">Ingat password Anda? <a href="{{ route('login.modern') }}" wire:navigate class="font-bold text-green-400 transition hover:text-green-300">Kembali ke login</a></p>
                <p class="mt-8 border-t border-white/[.07] pt-6 text-center text-xs text-[#55705e]">&copy; {{ date('Y') }} Koperasi Sinara Artha Naya</p>
            </div>
        </section>
    </main>
</div>
