<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0f0c">
    <meta name="description" content="Portal anggota Koperasi Sinara Artha Naya yang aman, modern, dan terpercaya.">
    <title>Koperasi Sinara Artha Naya</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&family=poppins:600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes reveal-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes glow-drift {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(24px, -18px, 0) scale(1.08); }
        }

        .reveal-up {
            opacity: 0;
            animation: reveal-up .8s cubic-bezier(.22, 1, .36, 1) forwards;
        }

        .brand-font {
            font-family: Poppins, Inter, sans-serif;
        }

        .page-pattern {
            background-image:
                radial-gradient(circle at 12% 5%, rgba(34, 197, 94, .11), transparent 28rem),
                radial-gradient(circle at 90% 28%, rgba(250, 204, 21, .07), transparent 25rem),
                linear-gradient(rgba(255, 255, 255, .018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .018) 1px, transparent 1px);
            background-size: auto, auto, 48px 48px, 48px 48px;
        }

        .hero-surface {
            background-image:
                linear-gradient(145deg, rgba(17, 26, 20, .86), rgba(10, 15, 12, .97)),
                url('{{ asset('images/bg_kartu_simpanan.jpg') }}');
            background-position: center;
            background-size: cover;
        }

        .glow-orb {
            animation: glow-drift 12s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal-up,
            .glow-orb {
                opacity: 1;
                animation: none;
                transform: none;
            }
        }
    </style>
</head>
<body class="page-pattern min-h-screen overflow-x-hidden bg-[#0a0f0c] font-sans text-[#f0faf4] antialiased">
    <div class="glow-orb pointer-events-none fixed -left-48 top-20 h-96 w-96 rounded-full bg-green-500/[.08] blur-3xl"></div>
    <div class="glow-orb pointer-events-none fixed -right-48 top-1/2 h-96 w-96 rounded-full bg-yellow-400/[.05] blur-3xl" style="animation-delay: -6s"></div>

    <header class="relative z-20 border-b border-white/[.07] bg-[#0a0f0c]/85 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="Beranda Koperasi Sinara Artha Naya">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white p-1.5 shadow-lg shadow-black/20">
                    <img src="{{ asset('images/logo_kospin.png') }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="leading-tight">
                    <span class="brand-font block text-sm font-bold tracking-tight text-white sm:text-base">SINARA ARTHA NAYA</span>
                    <span class="hidden text-[10px] font-medium text-[#a3c9b0] sm:block">Koperasi simpanan anggota</span>
                </span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ url('/user') }}" class="group inline-flex items-center gap-2 rounded-lg bg-green-500 px-4 py-2.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-lg hover:shadow-green-500/20 sm:px-5">
                        Dashboard
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login.modern') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-[#a3c9b0] transition duration-300 hover:bg-white/[.06] hover:text-white sm:px-4">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-green-500 px-4 py-2.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-lg hover:shadow-green-500/20 sm:px-5">
                            Daftar
                        </a>
                    @endif
                @endauth
            </div>
        </nav>
    </header>

    <main class="relative z-10">
        <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-16 sm:px-8 sm:py-20 lg:min-h-[680px] lg:grid-cols-[.92fr_1.08fr] lg:gap-16 lg:py-24">
            <div class="max-w-2xl">
                <span class="reveal-up inline-flex items-center gap-2 rounded-full border border-yellow-400/25 bg-yellow-400/[.08] px-3.5 py-2 text-[11px] font-bold uppercase tracking-[.15em] text-yellow-300" style="animation-delay: .08s">
                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-300"></span>
                    Koperasi Simpanan Profesional
                </span>

                <h1 class="brand-font reveal-up mt-6 text-4xl font-bold leading-[1.08] tracking-[-.035em] text-white sm:text-5xl lg:text-[3.75rem]" style="animation-delay: .16s">
                    Kelola simpanan dengan <span class="text-green-400">aman dan terencana.</span>
                </h1>

                <p class="reveal-up mt-6 max-w-xl text-base leading-7 text-[#a3c9b0] sm:text-lg sm:leading-8" style="animation-delay: .24s">
                    Portal digital Koperasi Sinara Artha Naya membantu anggota mengakses layanan finansial dengan lebih jelas, mudah, dan terpercaya.
                </p>

                <div class="reveal-up mt-7 flex flex-wrap gap-2" style="animation-delay: .32s">
                    <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-medium text-[#cce6d4]">Aman dan terarah</span>
                    <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-medium text-[#cce6d4]">Layanan terintegrasi</span>
                    <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-medium text-[#cce6d4]">Akses cepat</span>
                </div>

                <div class="reveal-up mt-9 flex flex-col gap-3 sm:flex-row" style="animation-delay: .4s">
                    @auth
                        <a href="{{ url('/user') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-6 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-1 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20">
                            Buka dashboard
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login.modern') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-6 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-1 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20">
                            Masuk ke akun anggota
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg border border-white/15 bg-white/[.03] px-6 py-3.5 text-sm font-bold text-white transition duration-300 hover:-translate-y-1 hover:border-green-400/50 hover:bg-white/[.07]">
                                Daftar anggota
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="reveal-up hero-surface relative overflow-hidden rounded-[1.75rem] border border-white/[.09] p-5 shadow-[0_30px_80px_rgba(0,0,0,.35)] sm:p-7" style="animation-delay: .22s">
                <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-green-500/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-16 -left-10 h-44 w-44 rounded-full bg-yellow-400/[.06] blur-2xl"></div>

                <div class="relative rounded-2xl border border-white/[.08] bg-[#111a14]/90 p-5 backdrop-blur-xl sm:p-7">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-[.18em] text-yellow-300">Portal Anggota</span>
                            <h2 class="brand-font mt-2 text-xl font-bold text-white sm:text-2xl">Layanan dalam satu akses</h2>
                            <p class="mt-2 max-w-md text-sm leading-6 text-[#a3c9b0]">Pantau layanan koperasi sesuai kebutuhan dan prioritas finansial Anda.</p>
                        </div>
                        <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-500 text-[#08110b] sm:flex">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>

                    <div class="mt-7 space-y-3">
                        <div class="group flex items-center gap-4 rounded-xl border border-white/[.07] bg-[#182118] p-4 transition duration-300 hover:border-green-400/30 hover:bg-[#1c291e]">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-500/15 text-green-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M5 11h14v8H5v-8zm3 4h3"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-white">Simpanan Anggota</p>
                                <p class="mt-1 text-xs text-[#a3c9b0]">Informasi simpanan yang jelas dan mudah dipantau.</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#61866d] transition group-hover:translate-x-1 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>

                        <div class="group flex items-center gap-4 rounded-xl border border-white/[.07] bg-[#182118] p-4 transition duration-300 hover:border-green-400/30 hover:bg-[#1c291e]">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-yellow-400/10 text-yellow-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-white">Riwayat Transaksi</p>
                                <p class="mt-1 text-xs text-[#a3c9b0]">Aktivitas tercatat secara rapi dan transparan.</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#61866d] transition group-hover:translate-x-1 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>

                        <div class="group flex items-center gap-4 rounded-xl border border-white/[.07] bg-[#182118] p-4 transition duration-300 hover:border-green-400/30 hover:bg-[#1c291e]">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-500/15 text-green-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-white">Keamanan Data</p>
                                <p class="mt-1 text-xs text-[#a3c9b0]">Akses akun dan informasi anggota tetap terlindungi.</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#61866d] transition group-hover:translate-x-1 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>

                    <blockquote class="mt-6 border-t border-white/[.08] pt-5 text-sm italic leading-6 text-[#a3c9b0]">
                        “{{ trim($quote, "\"\r\n ") }}”
                    </blockquote>
                </div>
            </div>
        </section>

        <section class="border-y border-white/[.06] bg-[#111a14]/55">
            <div class="mx-auto grid max-w-7xl gap-px px-5 py-14 sm:grid-cols-3 sm:px-8 lg:py-16">
                <article class="reveal-up border-white/[.07] py-5 sm:border-r sm:px-7 sm:py-2 sm:first:pl-0" style="animation-delay: .45s">
                    <span class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/15 text-green-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <h2 class="brand-font font-bold text-white">Simpanan aman</h2>
                    <p class="mt-2 text-sm leading-6 text-[#8cb399]">Pengelolaan layanan diarahkan agar nyaman, teratur, dan mudah dipahami.</p>
                </article>

                <article class="reveal-up border-white/[.07] py-5 sm:border-r sm:px-7 sm:py-2" style="animation-delay: .52s">
                    <span class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-400/10 text-yellow-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                    <h2 class="brand-font font-bold text-white">Akses cepat</h2>
                    <p class="mt-2 text-sm leading-6 text-[#8cb399]">Antarmuka ringkas membantu anggota menyelesaikan kebutuhan lebih efisien.</p>
                </article>

                <article class="reveal-up py-5 sm:px-7 sm:py-2 sm:last:pr-0" style="animation-delay: .59s">
                    <span class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/15 text-green-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <h2 class="brand-font font-bold text-white">Fokus anggota</h2>
                    <p class="mt-2 text-sm leading-6 text-[#8cb399]">Setiap fitur dibangun untuk pengalaman koperasi yang jelas dan transparan.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="relative z-10 border-t border-white/[.06] bg-[#080d0a] px-5 py-6 sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 text-center sm:flex-row sm:text-left">
            <p class="text-xs text-[#6f947a]">&copy; {{ date('Y') }} KOPERASI SINARA ARTHA NAYA. All rights reserved.</p>
            <p class="flex items-center gap-2 text-xs font-medium text-yellow-300/80">
                <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                Terdaftar dan diawasi oleh Dinas Koperasi
            </p>
        </div>
    </footer>
</body>
</html>
