<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#e7eee8" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#203426" media="(prefers-color-scheme: dark)">
    <title>{{ $title ?? 'Masuk' }} - Kospin Sinara Artha</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|plus-jakarta-sans:500,600,700,800|ibm-plex-mono:500,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        @keyframes auth-reveal {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes auth-glow {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(20px, -16px, 0) scale(1.08); }
        }

        .auth-brand-font {
            font-family: 'Plus Jakarta Sans', Inter, sans-serif;
        }

        .display-font {
            font-family: 'Plus Jakarta Sans', Inter, sans-serif;
        }

        .mono-font {
            font-family: 'IBM Plex Mono', ui-monospace, SFMono-Regular, monospace;
        }

        /* signature: growing savings sparkline */
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

        .auth-page {
            background-color: #e7eee8;
            background-image:
                radial-gradient(circle at 82% 10%, rgba(34, 197, 94, .14), transparent 25rem),
                linear-gradient(rgba(22, 101, 52, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(22, 101, 52, .04) 1px, transparent 1px);
            background-size: auto, 48px 48px, 48px 48px;
        }

        .auth-visual {
            background-image:
                linear-gradient(145deg, rgba(20, 58, 33, .76), rgba(12, 38, 22, .88)),
                url('{{ asset('images/bg_kartu_simpanan.jpg') }}');
            background-position: center;
            background-size: cover;
        }

        @media (prefers-color-scheme: dark) {
            .auth-page {
                background-color: #203426;
                background-image:
                    radial-gradient(circle at 82% 10%, rgba(74, 222, 128, .12), transparent 25rem),
                    linear-gradient(rgba(255, 255, 255, .025) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, .025) 1px, transparent 1px);
            }

            .auth-visual {
                background-image:
                    linear-gradient(145deg, rgba(27, 51, 34, .76), rgba(20, 32, 25, .9)),
                    url('{{ asset('images/bg_kartu_simpanan.jpg') }}');
            }
        }

        .auth-reveal {
            opacity: 0;
            animation: auth-reveal .8s cubic-bezier(.22, 1, .36, 1) forwards;
        }

        .auth-glow {
            animation: auth-glow 12s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-reveal,
            .auth-glow,
            .sparkline-path,
            .sparkline-fill,
            .sparkline-dot {
                opacity: 1;
                transform: none;
                animation: none;
            }
        }
    </style>
</head>
<body class="public-theme auth-page min-h-screen overflow-x-hidden font-sans text-[#f0faf4] antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
