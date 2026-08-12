<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e7eee8" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#203426" media="(prefers-color-scheme: dark)">
    <meta name="description" content="Portal anggota Koperasi Sinara Artha Naya yang aman, modern, dan terpercaya.">
    <title>Koperasi Sinara Artha Naya</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|plus-jakarta-sans:500,600,700,800|ibm-plex-mono:500,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --accent: #22c55e;
            --accent-soft: #4ade80;
            --gold: #fde047;
        }

        .display-font {
            font-family: 'Plus Jakarta Sans', Inter, sans-serif;
        }

        .mono-font {
            font-family: 'IBM Plex Mono', ui-monospace, SFMono-Regular, monospace;
        }

        /* ----- reveal on scroll ----- */
        .reveal {
            opacity: 0;
            transform: translateY(26px);
            transition:
                opacity .7s cubic-bezier(.22, 1, .36, 1),
                transform .7s cubic-bezier(.22, 1, .36, 1);
        }

        .reveal.is-visible {
            opacity: 1;
            transform: none;
        }

        /* ----- page backdrop pattern ----- */
        .page-pattern {
            background-image:
                radial-gradient(circle at 12% 0%, rgba(34, 197, 94, .14), transparent 30rem),
                radial-gradient(circle at 88% 22%, rgba(250, 204, 21, .08), transparent 26rem),
                radial-gradient(circle at 55% 90%, rgba(34, 197, 94, .07), transparent 24rem),
                linear-gradient(rgba(22, 101, 52, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(22, 101, 52, .045) 1px, transparent 1px);
            background-size: auto, auto, auto, 48px 48px, 48px 48px;
        }

        @media (prefers-color-scheme: dark) {
            .page-pattern {
                background-image:
                    radial-gradient(circle at 12% 0%, rgba(74, 222, 128, .12), transparent 30rem),
                    radial-gradient(circle at 88% 22%, rgba(250, 204, 21, .06), transparent 26rem),
                    radial-gradient(circle at 55% 90%, rgba(34, 197, 94, .06), transparent 24rem),
                    linear-gradient(rgba(255, 255, 255, .025) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, .025) 1px, transparent 1px);
                background-size: auto, auto, auto, 48px 48px, 48px 48px;
            }
        }

        .hero-surface {
            background-image:
                linear-gradient(155deg, rgba(15, 48, 27, .82), rgba(10, 34, 20, .92)),
                url('{{ asset('images/bg_kartu_simpanan.jpg') }}');
            background-position: center;
            background-size: cover;
        }

        @media (prefers-color-scheme: dark) {
            .hero-surface {
                background-image:
                    linear-gradient(155deg, rgba(22, 46, 29, .82), rgba(15, 28, 19, .94)),
                    url('{{ asset('images/bg_kartu_simpanan.jpg') }}');
            }
        }

        /* ----- ambient drift ----- */
        @keyframes drift {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(22px, -16px, 0) scale(1.06); }
        }

        .glow-orb {
            animation: drift 14s ease-in-out infinite;
        }

        /* ----- signature: growing savings sparkline ----- */
        .sparkline-path {
            stroke-dasharray: 1;
            stroke-dashoffset: 1;
            animation: draw-line 2.4s cubic-bezier(.22, 1, .36, 1) .45s forwards;
        }

        .sparkline-fill {
            opacity: 0;
            animation: fade-fill .8s ease .9s forwards;
        }

        .sparkline-dot {
            opacity: 0;
            transform: scale(0);
            transform-origin: center;
            transform-box: fill-box;
            animation: pop-dot .5s cubic-bezier(.34, 1.56, .64, 1) 1.5s forwards;
        }

        @keyframes draw-line {
            to { stroke-dashoffset: 0; }
        }

        @keyframes fade-fill {
            to { opacity: 1; }
        }

        @keyframes pop-dot {
            to { opacity: 1; transform: scale(1); }
        }

        /* horizontal rule that draws across section headers */
        @keyframes grow-x {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        .rule-anim.is-visible .section-rule {
            transform-origin: left center;
            animation: grow-x 1s cubic-bezier(.22, 1, .36, 1) .15s forwards;
        }

        .section-rule {
            transform: scaleX(0);
        }

        /* ----- reduced motion ----- */
        @media (prefers-reduced-motion: reduce) {
            .reveal,
            .sparkline-path,
            .sparkline-fill,
            .sparkline-dot {
                opacity: 1;
                transform: none;
                animation: none;
            }

            .glow-orb {
                animation: none;
            }

            .section-rule,
            .rule-anim.is-visible .section-rule {
                transform: scaleX(1);
                animation: none;
            }

            * {
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body class="public-theme page-pattern relative min-h-screen overflow-x-hidden bg-[#0a0f0c] font-sans text-[#f0faf4] antialiased">
    <div class="glow-orb pointer-events-none fixed -left-48 top-16 z-0 h-96 w-96 rounded-full bg-green-500/[.08] blur-3xl"></div>
    <div class="glow-orb pointer-events-none fixed -right-48 top-1/3 z-0 h-96 w-96 rounded-full bg-yellow-400/[.05] blur-3xl" style="animation-delay: -7s"></div>

    <!-- ============ HEADER ============ -->
    <header class="sticky top-0 z-30 border-b border-white/[.07] bg-[#0a0f0c]/80 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-3.5 sm:px-8" aria-label="Navigasi utama">
            <a href="{{ url('/') }}" class="group flex items-center gap-3" aria-label="Beranda Koperasi Sinara Artha Naya">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white p-1.5 shadow-lg shadow-black/20 transition duration-300 group-hover:shadow-green-500/20">
                    <img src="{{ asset('images/logo_kospin.png') }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="leading-tight">
                    <span class="display-font block text-sm font-bold tracking-tight text-white sm:text-base">SINARA ARTHA NAYA</span>
                    <span class="hidden text-[10px] font-medium text-[#a3c9b0] sm:block">Koperasi simpanan anggota</span>
                </span>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ url('/user') }}" class="group inline-flex items-center gap-2 rounded-lg bg-green-500 px-4 py-2.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-lg hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400 sm:px-5">
                        Dashboard
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login.modern') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-[#a3c9b0] transition duration-300 hover:bg-white/[.06] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400 sm:px-4">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-green-500 px-4 py-2.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-0.5 hover:bg-green-400 hover:shadow-lg hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400 sm:px-5">
                            Daftar
                        </a>
                    @endif
                @endauth
            </div>
        </nav>
    </header>

    <!-- ============ HERO ============ -->
    <main class="relative z-10">
        <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-20 pt-14 sm:px-8 sm:pt-20 lg:min-h-[688px] lg:grid-cols-[.94fr_1.06fr] lg:gap-16 lg:pb-24 lg:pt-24">
            <!-- message -->
            <div class="max-w-2xl">
                <span class="reveal mono-font inline-flex items-center gap-2.5 rounded-full border border-yellow-400/25 bg-yellow-400/[.08] px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[.14em] text-yellow-300" style="transition-delay:.05s">
                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-300"></span>
                    Koperasi Simpanan Profesional
                </span>

                <h1 class="display-font reveal mt-6 text-[clamp(2.2rem,4.6vw,3.6rem)] font-bold leading-[1.06] tracking-[-.03em] text-white" style="transition-delay:.12s">
                    Kelola simpanan dengan <span class="text-green-400">aman dan terencana.</span>
                </h1>

                <p class="reveal mt-6 max-w-xl text-base leading-7 text-[#a3c9b0] sm:text-lg sm:leading-8" style="transition-delay:.19s">
                    Portal digital Koperasi Sinara Artha Naya membantu anggota mengakses layanan finansial dengan lebih jelas, mudah, dan terpercaya.
                </p>

                <div class="reveal mt-7 flex flex-wrap gap-2" style="transition-delay:.26s">
                    <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-medium text-[#cce6d4]">Aman dan terarah</span>
                    <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-medium text-[#cce6d4]">Layanan terintegrasi</span>
                    <span class="rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-medium text-[#cce6d4]">Akses cepat</span>
                </div>

                <div class="reveal mt-9 flex flex-col gap-3 sm:flex-row" style="transition-delay:.33s">
                    @auth
                        <a href="{{ url('/user') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-6 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-1 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                            Buka dashboard
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login.modern') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-6 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-1 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                            Masuk ke akun anggota
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg border border-white/15 bg-white/[.03] px-6 py-3.5 text-sm font-bold text-white transition duration-300 hover:-translate-y-1 hover:border-green-400/50 hover:bg-white/[.07] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                                Daftar anggota
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- passbook / ledger card -->
            <div class="reveal hero-surface relative overflow-hidden rounded-[1.75rem] border border-white/[.09] p-5 shadow-[0_30px_80px_rgba(0,0,0,.35)] sm:p-7" style="transition-delay:.18s">
                <div class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-green-500/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-16 -left-10 h-44 w-44 rounded-full bg-yellow-400/[.06] blur-2xl"></div>

                <div class="relative rounded-2xl border border-white/[.08] bg-[#111a14]/90 p-6 backdrop-blur-xl sm:p-7">
                    <!-- book header -->
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <span class="mono-font text-[10px] font-semibold uppercase tracking-[.18em] text-yellow-300">Buku Simpanan</span>
                            <h2 class="display-font mt-2 text-xl font-bold text-white sm:text-2xl">Pertumbuhan simpanan</h2>
                            <p class="mt-2 max-w-md text-sm leading-6 text-[#a3c9b0]">Setiap setoran tercatat rapi — simpanan anggota yang dikelola bersama dan terus bertumbuh.</p>
                        </div>
                        <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-500 text-[#08110b] sm:flex" aria-hidden="true">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </span>
                    </div>

                    <!-- growth sparkline -->
                    <div class="mt-7">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <span class="mono-font text-[10px] font-medium uppercase tracking-[.16em] text-[#a3c9b0]">Saldo anggota</span>
                                <div class="mono-font mt-1 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Tumbuh <span class="text-green-400">stabil</span></div>
                            </div>
                            <span class="mono-font inline-flex items-center gap-1.5 text-xs font-semibold text-green-400" aria-label="tren positif">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                Terus naik
                            </span>
                        </div>

                        <svg class="mt-4 h-24 w-full" viewBox="0 0 600 160" preserveAspectRatio="none" role="img" aria-label="Grafik pertumbuhan simpanan">
                            <defs>
                                <linearGradient id="sparkFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#22c55e" stop-opacity="0.28"/>
                                    <stop offset="100%" stop-color="#22c55e" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <path class="sparkline-fill" d="M0,132 L88,118 L176,124 L264,92 L352,104 L440,62 L528,40 L600,24 L600,160 L0,160 Z" fill="url(#sparkFill)"/>
                            <path class="sparkline-path" d="M0,132 C40,124 60,120 88,118 C120,115 148,126 176,124 C210,121 230,100 264,92 C300,83 322,110 352,104 C384,97 410,78 440,62 C472,45 500,44 528,40 C558,36 580,30 600,24"
                                  fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" pathLength="1"/>
                            <circle class="sparkline-dot" cx="600" cy="24" r="6" fill="#22c55e" stroke="#0a0f0c" stroke-width="3"/>
                        </svg>
                    </div>

                    <!-- ledger lines -->
                    <div class="mt-6 border-t border-white/[.08] pt-2">
                        <div class="group flex items-center gap-4 rounded-xl p-3 transition duration-300 hover:bg-[#182118]/70">
                            <span class="mono-font w-9 shrink-0 text-[9px] font-semibold uppercase tracking-widest text-[#6f947a]">01</span>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-500/15 text-green-400" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M5 11h14v8H5v-8zm3 4h3"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-white">Simpanan Anggota</p>
                                <p class="mt-0.5 text-xs text-[#a3c9b0]">Informasi simpanan yang jelas dan mudah dipantau.</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#61866d] transition group-hover:translate-x-1 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>

                        <div class="group flex items-center gap-4 rounded-xl p-3 transition duration-300 hover:bg-[#182118]/70">
                            <span class="mono-font w-9 shrink-0 text-[9px] font-semibold uppercase tracking-widest text-[#6f947a]">02</span>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-yellow-400/10 text-yellow-300" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-white">Riwayat Transaksi</p>
                                <p class="mt-0.5 text-xs text-[#a3c9b0]">Aktivitas tercatat secara rapi dan transparan.</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#61866d] transition group-hover:translate-x-1 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>

                        <div class="group flex items-center gap-4 rounded-xl p-3 transition duration-300 hover:bg-[#182118]/70">
                            <span class="mono-font w-9 shrink-0 text-[9px] font-semibold uppercase tracking-widest text-[#6f947a]">03</span>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-500/15 text-green-400" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-white">Keamanan Data</p>
                                <p class="mt-0.5 text-xs text-[#a3c9b0]">Akses akun dan informasi anggota tetap terlindungi.</p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-[#61866d] transition group-hover:translate-x-1 group-hover:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>

                    <blockquote class="mt-5 border-t border-white/[.08] pt-5 text-sm italic leading-6 text-[#a3c9b0]">
                        {{ trim($quote, "\"“” \r\n\t") }}
                    </blockquote>
                </div>
            </div>
        </section>

        <!-- ============ LAYANAN ============ -->
        <section class="reveal rule-anim border-y border-white/[.06] bg-[#111a14]/55">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-20">
                <div class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="mono-font text-[11px] font-semibold uppercase tracking-[.2em] text-yellow-300">Layanan</span>
                        <h2 class="display-font mt-3 max-w-xl text-2xl font-bold tracking-tight text-white sm:text-3xl">Satu akun untuk setiap kebutuhan finansial anggota</h2>
                    </div>
                    <p class="max-w-xs text-sm leading-6 text-[#8cb399]">Layanan dirancang sederhana, transparan, dan sesuai kebutuhan nyata anggota koperasi.</p>
                </div>

                <div class="grid gap-px overflow-hidden rounded-2xl border border-white/[.07] bg-white/[.04] sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $services = [
                            ['label' => 'Simpanan', 'desc' => 'Menabung rutin untuk masa depan yang terencana dan aman.', 'accent' => 'green'],
                            ['label' => 'Pinjaman', 'desc' => 'Akses pinjaman ringan dan jelas untuk kebutuhan anggota.', 'accent' => 'yellow'],
                            ['label' => 'Deposito', 'desc' => 'Simpanan berjangka dengan imbal hasil yang tertata.', 'accent' => 'green'],
                            ['label' => 'Gadai Emas', 'desc' => 'Memperoleh dana dengan tetap memegang aset berharga.', 'accent' => 'yellow'],
                        ];
                    @endphp

                    @foreach ($services as $i => $s)
                        <a href="{{ auth()->check() ? url('/user') : route('login.modern') }}" class="group relative flex flex-col gap-5 bg-[#182118]/40 p-6 transition duration-300 hover:bg-[#1c291e] focus-visible:outline focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-green-400">
                            <div class="flex items-start justify-between">
                                <span class="mono-font text-[10px] font-semibold uppercase tracking-widest text-[#6f947a]">0{{ $i + 1 }}</span>
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $s['accent'] === 'green' ? 'bg-green-500/15 text-green-400' : 'bg-yellow-400/10 text-yellow-300' }}">
                                    @if ($s['label'] === 'Simpanan')
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M5 11h14v8H5v-8zm3 4h3"/></svg>
                                    @elseif ($s['label'] === 'Pinjaman')
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @elseif ($s['label'] === 'Deposito')
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @endif
                                </span>
                            </div>
                            <div>
                                <h3 class="display-font text-lg font-bold text-white">{{ $s['label'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#a3c9b0]">{{ $s['desc'] }}</p>
                            </div>
                            <span class="mono-font mt-auto inline-flex items-center gap-2 text-xs font-semibold text-[#6f947a] transition duration-300 group-hover:text-green-400">
                                Pelajari
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="section-rule mt-10 h-px w-full bg-gradient-to-r from-green-500/40 via-white/[.07] to-transparent"></div>
            </div>
        </section>

        <!-- ============ CARA BERGABUNG ============ -->
        <section class="reveal">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-20">
                <div class="mb-12 max-w-xl">
                    <span class="mono-font text-[11px] font-semibold uppercase tracking-[.2em] text-yellow-300">Cara bergabung</span>
                    <h2 class="display-font mt-3 text-2xl font-bold tracking-tight text-white sm:text-3xl">Tiga langkah menjadi anggota</h2>
                    <p class="mt-4 text-sm leading-6 text-[#8cb399]">Urutan yang jelas, tanpa langkah rumit — mulai dari pendaftaran hingga simpanan pertama Anda tercatat.</p>
                </div>

                <ol class="grid gap-6 md:grid-cols-3">
                    @php
                        $steps = [
                            ['title' => 'Daftar akun', 'desc' => 'Buat akun anggota secara online hanya dalam beberapa menit.'],
                            ['title' => 'Verifikasi data', 'desc' => 'Petugas koperasi memverifikasi data keanggotaan Anda dengan cepat.'],
                            ['title' => 'Mulai menabung', 'desc' => 'Masuk ke dashboard dan mulai mengelola simpanan Anda.'],
                        ];
                    @endphp

                    @foreach ($steps as $i => $st)
                        <li class="reveal group relative rounded-2xl border border-white/[.07] bg-[#111a14]/55 p-6 transition duration-300 hover:-translate-y-1 hover:border-green-400/30 hover:bg-[#182118]/55 hover:shadow-xl hover:shadow-black/20" style="transition-delay:{{ $i * 0.08 }}s">
                            <div class="flex items-center gap-4">
                                <span class="mono-font flex h-11 w-11 items-center justify-center rounded-xl bg-green-500/15 text-sm font-semibold text-green-400 ring-1 ring-inset ring-green-400/20">0{{ $i + 1 }}</span>
                                <h3 class="display-font text-base font-bold text-white">{{ $st['title'] }}</h3>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-[#a3c9b0]">{{ $st['desc'] }}</p>

                            @if (! $loop->last)
                                <span class="mono-font absolute -right-4 top-1/2 z-10 hidden -translate-y-1/2 text-base font-semibold text-green-400/40 md:block" aria-hidden="true">&#8594;</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <!-- ============ CTA ============ -->
        <section class="reveal">
            <div class="mx-auto max-w-7xl px-5 pb-20 sm:px-8 lg:pb-24">
                <div class="hero-surface relative overflow-hidden rounded-[1.75rem] border border-white/[.09] px-6 py-12 text-center shadow-[0_30px_80px_rgba(0,0,0,.35)] sm:px-12 sm:py-16">
                    <div class="pointer-events-none absolute -left-20 -top-20 h-56 w-56 rounded-full bg-green-500/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 -right-16 h-56 w-56 rounded-full bg-yellow-400/[.07] blur-2xl"></div>

                    <span class="mono-font text-[11px] font-semibold uppercase tracking-[.2em] text-yellow-300">Siap bergabung?</span>
                    <h2 class="display-font mx-auto mt-4 max-w-2xl text-2xl font-bold leading-tight tracking-tight text-white sm:text-3xl">Mulai kelola simpanan Anda dengan tenang dan terencana.</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-[#a3c9b0] sm:text-base">Gabung sebagai anggota dan akses seluruh layanan koperasi melalui satu akun yang aman.</p>

                    <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        @auth
                            <a href="{{ url('/user') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-7 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-1 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                                Buka dashboard
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 rounded-lg bg-green-500 px-7 py-3.5 text-sm font-bold text-[#08110b] transition duration-300 hover:-translate-y-1 hover:bg-green-400 hover:shadow-xl hover:shadow-green-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                                Daftar sebagai anggota
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                            @if (Route::has('login.modern'))
                                <a href="{{ route('login.modern') }}" class="inline-flex items-center justify-center rounded-lg border border-white/15 bg-white/[.03] px-7 py-3.5 text-sm font-bold text-white transition duration-300 hover:-translate-y-1 hover:border-green-400/50 hover:bg-white/[.07] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-400">
                                    Masuk anggota
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ============ FOOTER ============ -->
    <footer class="relative z-10 border-t border-white/[.06] bg-[#080d0a] px-5 py-6 sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 text-center sm:flex-row sm:text-left">
            <p class="text-xs text-[#6f947a]">&copy; {{ date('Y') }} KOPERASI SINARA ARTHA NAYA. All rights reserved.</p>
            <p class="flex items-center gap-2 text-xs font-medium text-yellow-300/80">
                <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                Terdaftar dan diawasi oleh Dinas Koperasi
            </p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            var reveals = document.querySelectorAll('.reveal, .rule-anim');
            if (reduce || !('IntersectionObserver' in window)) {
                reveals.forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });

            reveals.forEach(function (el) { io.observe(el); });
        });
    </script>
</body>
</html>
