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
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Fallback untuk styling dasar jika Vite belum di-build -->
            <style>
                /*! tailwindcss v3.4.1 | MIT License | https://tailwindcss.com */*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}::after,::before{--tw-content:''}:host,html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:Figtree,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";font-feature-settings:normal;font-variation-settings:normal;-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}[type=button],[type=reset],[type=submit],button{-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]{display:none}
            </style>
            <style>
                /* Tambahan styling kustom */
                .min-h-screen { min-height: 100vh; }
                .bg-gray-100 { background-color: #f3f4f6; }
                .dark\:bg-gray-900 { background-color: #111827; }
                .flex { display: flex; }
                .flex-col { flex-direction: column; }
                .items-center { align-items: center; }
                .justify-center { justify-content: center; }
                .w-full { width: 100%; }
                .max-w-md { max-width: 28rem; }
                .bg-white { background-color: #ffffff; }
                .p-6 { padding: 1.5rem; }
                .rounded-lg { border-radius: 0.5rem; }
                .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
                .text-gray-700 { color: #374151; }
                .dark\:text-gray-300 { color: #d1d5db; }
                .mt-4 { margin-top: 1rem; }
                .mb-4 { margin-bottom: 1rem; }
                .text-center { text-align: center; }
                .font-medium { font-weight: 500; }
                .text-sm { font-size: 0.875rem; }
                .block { display: block; }
                input[type="email"],
                input[type="password"] {
                    width: 100%;
                    padding: 0.5rem;
                    border: 1px solid #d1d5db;
                    border-radius: 0.375rem;
                    margin-top: 0.25rem;
                }
                button[type="submit"] {
                    width: 100%;
                    padding: 0.5rem 1rem;
                    background-color: #2563eb;
                    color: white;
                    border-radius: 0.375rem;
                    font-weight: 500;
                    margin-top: 1rem;
                }
                button[type="submit"]:hover {
                    background-color: #1d4ed8;
                }
            </style>
        @endif
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-6">
            <div class="w-full max-w-md">
                <!-- Alert Container -->
                <div id="alert-container" class="mb-4 hidden">
                    <div id="success-alert" class="p-4 mb-4 rounded-lg bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span id="alert-message"></span>
                        </div>
                    </div>
                </div>

                <!-- Card Container -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 sm:p-8 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white">
                            Reset Password
                        </h2>
                        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                            Masukkan password baru Anda
                        </p>
                    </div>

                    <!-- Form Section -->
                    <div class="p-6 sm:p-8 space-y-6">
                        <form id="reset-password-form" method="POST" action="{{ route('password.update') }}" class="space-y-6">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <!-- Email Field -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email
                                </label>
                                <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus
                                    class="block w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600
                                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                           dark:bg-gray-700 dark:text-white dark:placeholder-gray-400
                                           transition duration-150 ease-in-out">
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Password Baru
                                </label>
                                <input id="password" type="password" name="password" required
                                    class="block w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600
                                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                           dark:bg-gray-700 dark:text-white dark:placeholder-gray-400
                                           transition duration-150 ease-in-out">
                            </div>

                            <!-- Confirm Password Field -->
                            <div class="space-y-2">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Konfirmasi Password
                                </label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    class="block w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600
                                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                           dark:bg-gray-700 dark:text-white dark:placeholder-gray-400
                                           transition duration-150 ease-in-out">
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full px-4 py-3 text-sm font-semibold text-white
                                           bg-blue-600 rounded-lg hover:bg-blue-700
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                           transition duration-150 ease-in-out
                                           dark:bg-blue-500 dark:hover:bg-blue-600">
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambahkan script di bagian bawah body -->
        <script>
            document.getElementById('reset-password-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

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
                    const alertContainer = document.getElementById('alert-container');
                    const alertMessage = document.getElementById('alert-message');

                    if (data.status) {
                        // Sukses
                        alertContainer.classList.remove('hidden');
                        alertMessage.textContent = data.message;

                        // Reset form
                        this.reset();

                        // Redirect setelah 2 detik
                        setTimeout(() => {
                            window.location.href = '/'; // atau URL lain yang diinginkan
                        }, 2000);
                    } else {
                        // Error
                        alertContainer.classList.remove('hidden');
                        alertContainer.querySelector('div').classList.remove('bg-green-100', 'dark:bg-green-800', 'text-green-700', 'dark:text-green-300');
                        alertContainer.querySelector('div').classList.add('bg-red-100', 'dark:bg-red-800', 'text-red-700', 'dark:text-red-300');
                        alertMessage.textContent = data.message || 'Terjadi kesalahan';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertContainer = document.getElementById('alert-container');
                    const alertMessage = document.getElementById('alert-message');

                    alertContainer.classList.remove('hidden');
                    alertContainer.querySelector('div').classList.remove('bg-green-100', 'dark:bg-green-800', 'text-green-700', 'dark:text-green-300');
                    alertContainer.querySelector('div').classList.add('bg-red-100', 'dark:bg-red-800', 'text-red-700', 'dark:text-red-300');
                    alertMessage.textContent = 'Terjadi kesalahan pada server';
                });
            });
        </script>
    </body>
</html>
