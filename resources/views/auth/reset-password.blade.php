<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Reset Password - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-6">
            <div class="w-full max-w-md">
                <!-- Form Container -->
                <div id="form-container" class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 text-center">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Reset Password
                        </h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Masukkan password baru Anda
                        </p>
                    </div>

                    <!-- Form Section -->
                    <div class="px-6 pb-6">
                        <form id="reset-password-form" method="POST" action="{{ route('password.update') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                    Email
                                </label>
                                <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <!-- Password Field -->
                            <div>
                                <label for="password" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                    Password Baru
                                </label>
                                <input id="password" type="password" name="password" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <!-- Confirm Password Field -->
                            <div>
                                <label for="password_confirmation" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                    Konfirmasi Password
                                </label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6">
                                <button type="submit"
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Success Message Container (Initially Hidden) -->
                <div id="success-container" class="hidden">
                    <div class="mt-4 bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden text-center p-6">
                        <div class="flex justify-center mb-4">
                            <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Password Berhasil Direset
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Silakan login menggunakan password baru Anda
                        </p>
                        <div class="mt-4">
                            <a href="/" class="text-sm text-blue-600 hover:text-blue-700">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('reset-password-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const formContainer = document.getElementById('form-container');
                const successContainer = document.getElementById('success-container');

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        formContainer.classList.add('hidden');
                        successContainer.classList.remove('hidden');
                        successContainer.scrollIntoView({ behavior: 'smooth' });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        </script>
    </body>
</html>
