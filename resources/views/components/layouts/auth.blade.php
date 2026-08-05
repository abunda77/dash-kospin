<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#0a0f0c">
    <title>{{ $title ?? 'Masuk' }} - Kospin Sinara Artha</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&family=poppins:600,700&display=swap" rel="stylesheet">

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
            font-family: Poppins, Inter, sans-serif;
        }

        .auth-page {
            background-color: #0a0f0c;
            background-image:
                radial-gradient(circle at 82% 10%, rgba(34, 197, 94, .09), transparent 25rem),
                linear-gradient(rgba(255, 255, 255, .018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .018) 1px, transparent 1px);
            background-size: auto, 48px 48px, 48px 48px;
        }

        .auth-visual {
            background-image:
                linear-gradient(145deg, rgba(17, 26, 20, .84), rgba(10, 15, 12, .97)),
                url('{{ asset('images/bg_kartu_simpanan.jpg') }}');
            background-position: center;
            background-size: cover;
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
            .auth-glow {
                opacity: 1;
                animation: none;
                transform: none;
            }
        }
    </style>
</head>
<body class="auth-page min-h-screen overflow-x-hidden font-sans text-[#f0faf4] antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
