<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Generate Dynamic QRIS
            </x-slot>
            <x-slot name="description">
                Convert your static QRIS code to dynamic QRIS with custom amount and fee.
            </x-slot>

            <form wire:submit="generate" class="space-y-6">
                {{ $this->form }}

                <div class="flex gap-3">
                    <x-filament::button type="submit" color="primary">
                        <x-heroicon-o-sparkles class="w-5 h-5 mr-2" />
                        Generate Dynamic QRIS
                    </x-filament::button>

                    @if ($dynamicQris)
                        <x-filament::button type="button" wire:click="resetForm" color="gray">
                            <x-heroicon-o-arrow-path class="w-5 h-5 mr-2" />
                            Reset
                        </x-filament::button>
                    @endif
                </div>
            </form>
        </x-filament::section>

        {{-- Result Section --}}
        @if ($dynamicQris)
            <x-filament::section>
                <x-slot name="heading">
                    Generated Dynamic QRIS
                </x-slot>

                <div class="space-y-6">
                    {{-- Merchant Info --}}
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">Merchant Name</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $merchantName }}
                        </div>
                    </div>

                    {{-- QR Code Display --}}
                    <div
                        class="flex flex-col items-center justify-center p-6 bg-white dark:bg-gray-900 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                        @if (session('last_generated_qr'))
                            <img src="{{ asset('storage/qris-generated/' . session('last_generated_qr')) }}"
                                alt="Dynamic QRIS" class="mb-4 rounded-lg shadow-md" style="max-width: 400px;">
                        @else
                            <div id="qrcode" class="mb-4"></div>
                        @endif
                        <div class="text-sm text-gray-600 dark:text-gray-400 text-center">
                            Amount: <span class="font-semibold">Rp
                                {{ number_format($data['amount'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- QRIS String --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dynamic QRIS String
                        </label>
                        <div class="relative">
                            <textarea readonly rows="4"
                                class="w-full px-3 py-2 text-sm font-mono bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:text-white"
                                id="qris-string">{{ $dynamicQris }}</textarea>
                            <button type="button" onclick="copyToClipboard()"
                                class="absolute top-2 right-2 px-3 py-1 text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md transition-colors">
                                Copy
                            </button>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3">
                        @if (session('last_generated_qr'))
                            <x-filament::button type="button" color="success" wire:click="downloadImage">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                                Download QR Image
                            </x-filament::button>
                        @else
                            <x-filament::button type="button" color="success" onclick="downloadQR()">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                                Download QR Code
                            </x-filament::button>
                        @endif

                        <x-filament::button type="button" color="gray" onclick="printQR()">
                            <x-heroicon-o-printer class="w-5 h-5 mr-2" />
                            Print
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>

    @if ($dynamicQris)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    generateQRCode();
                });

                // Listen for Livewire updates
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', () => {
                        setTimeout(() => {
                            if (document.getElementById('qrcode')) {
                                generateQRCode();
                            }
                        }, 100);
                    });
                });

                function generateQRCode() {
                    const qrContainer = document.getElementById('qrcode');
                    if (!qrContainer) return;

                    // Clear existing QR code
                    qrContainer.innerHTML = '';

                    // Generate new QR code
                    QRCode.toCanvas(
                        '{{ $dynamicQris }}', {
                            width: 300,
                            margin: 2,
                            color: {
                                dark: '#000000',
                                light: '#FFFFFF'
                            }
                        },
                        function(error, canvas) {
                            if (error) {
                                console.error(error);
                                return;
                            }
                            qrContainer.appendChild(canvas);
                        }
                    );
                }

                function copyToClipboard() {
                    const textarea = document.getElementById('qris-string');
                    textarea.select();
                    document.execCommand('copy');

                    // Show notification
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message: 'QRIS string copied to clipboard!',
                            type: 'success'
                        }
                    }));
                }

                function downloadQR() {
                    const canvas = document.querySelector('#qrcode canvas');
                    if (!canvas) return;

                    const link = document.createElement('a');
                    link.download = 'qris-dynamic-{{ now()->format('YmdHis') }}.png';
                    link.href = canvas.toDataURL();
                    link.click();
                }

                function printQR() {
                    const canvas = document.querySelector('#qrcode canvas');
                    if (!canvas) return;

                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                    <html>
                        <head>
                            <title>Print QRIS</title>
                            <style>
                                body {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                    min-height: 100vh;
                                    margin: 0;
                                    font-family: sans-serif;
                                }
                                .merchant { font-size: 24px; font-weight: bold; margin-bottom: 20px; }
                                .amount { font-size: 18px; margin-top: 20px; }
                                @media print {
                                    body { padding: 20px; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="merchant">{{ $merchantName }}</div>
                            <img src="${canvas.toDataURL()}" />
                            <div class="amount">Amount: Rp {{ number_format($data['amount'] ?? 0, 0, ',', '.') }}</div>
                        </body>
                    </html>
                `);
                    printWindow.document.close();
                    printWindow.focus();
                    setTimeout(() => {
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                }
            </script>
        @endpush
    @endif
</x-filament-panels::page>
