<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Koperasi Sinarartha</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-br from-slate-900 to-slate-800">
        <div class="relative min-h-screen sm:flex sm:justify-center sm:items-center">
            <!-- Navigation -->
            @if (Route::has('login'))
                <div class="z-10 p-6 text-right sm:fixed sm:top-0 sm:right-0">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-white hover:text-slate-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-slate-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-white hover:text-slate-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-slate-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-white hover:text-slate-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-slate-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <!-- Main Content -->
            <div class="p-6 mx-auto max-w-7xl lg:p-8">
                <div class="flex justify-center">
                    <img src="/images/logo_sinarartha_dark.png" alt="Logo Sinarartha Light" class="block w-auto h-auto dark:hidden">
                    <img src="/images/logo_sinarartha_light.png" alt="Logo Sinarartha Dark" class="hidden w-auto h-auto dark:block">
                </div>

                <div class="mt-16">
                    <div class="grid grid-cols-1 gap-6 lg:gap-8">
                        <div class="scale-100 p-6 bg-gray-300 dark:bg-slate-800/50 dark:bg-gradient-to-bl from-slate-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250">
                            <div>
                                <div class="flex flex-col items-center justify-center py-8">
                                    <p class="max-w-2xl font-serif text-2xl italic leading-relaxed text-center text-slate-600 dark:text-slate-200">
                                        "{{ $quote }}"
                                    </p>
                                </div>



                                {{-- <div class="flex gap-4 mt-8">
                                    <button class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors rounded-md ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 bg-slate-900 text-slate-50 hover:bg-slate-900/90 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90">
                                        Primary Button
                                    </button>

                                    <button class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors bg-white border rounded-md ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 border-slate-200 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-800 dark:hover:text-slate-50">
                                        Secondary Button
                                    </button>

                                    <button class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border rounded-md ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 border-slate-200 text-slate-900 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:text-slate-50 dark:hover:bg-slate-800 dark:hover:text-slate-50">
                                        Outline Button
                                    </button>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
