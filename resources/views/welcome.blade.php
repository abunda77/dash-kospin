<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi Sinarartha</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center min-h-screen font-sans text-white bg-gradient-to-br from-blue-950 to-blue-900">
    @if (Route::has('login'))
        <div class="fixed top-4 right-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="ml-4 font-semibold text-white transition-opacity hover:opacity-80">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="ml-4 font-semibold text-white transition-opacity hover:opacity-80">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="ml-4 font-semibold text-white transition-opacity hover:opacity-80">Register</a>
                @endif
            @endauth
        </div>
    @endif

    <div class="p-4 text-center md:p-6 lg:p-8">
        <div class="mb-4 md:mb-6 lg:mb-8">
            <img src="/images/logo_sinarartha_dark.png" alt="Logo Sinarartha" class="w-full max-w-[300px] md:max-w-[450px] lg:max-w-[600px] h-auto mx-auto">
        </div>
        <div class="max-w-[90%] md:max-w-2xl lg:max-w-3xl mx-auto text-lg md:text-2xl lg:text-2xl italic leading-relaxed text-slate-300">
            "{{ $quote }}"
        </div>
    </div>
</body>
</html>
