<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Progress Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">📊 Export Progress Monitor</h1>
            
            <div x-data="progressMonitor()" x-init="init()">
                <!-- Progress Key Input -->
                <div class="mb-6">
                    <label for="progressKey" class="block text-sm font-medium text-gray-700 mb-2">
                        Progress Key
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="progressKey"
                            x-model="progressKey"
                            placeholder="Enter progress key (e.g., pdf_export_progress_...)"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <button 
                            @click="startMonitoring()"
                            :disabled="!progressKey || isMonitoring"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            <span x-show="!isMonitoring">Monitor</span>
                            <span x-show="isMonitoring">Monitoring...</span>
                        </button>
                    </div>
                </div>

                <!-- Progress Display -->
                <div x-show="progress" class="space-y-4">
                    <!-- Progress Bar -->
                    <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                        <div 
                            class="bg-blue-600 h-full transition-all duration-300 ease-out"
                            :style="`width: ${progress?.percent || 0}%`"
                        ></div>
                    </div>
                    
                    <!-- Progress Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600" x-text="progress?.percent || 0"></div>
                            <div class="text-sm text-gray-600">Percent</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold text-green-600" x-text="progress?.processed || 0"></div>
                            <div class="text-sm text-gray-600">Processed</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold text-gray-600" x-text="progress?.total || 0"></div>
                            <div class="text-sm text-gray-600">Total</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-lg font-bold" :class="getStatusColor()" x-text="progress?.status || 'Unknown'"></div>
                            <div class="text-sm text-gray-600">Status</div>
                        </div>
                    </div>
                    
                    <!-- Time Information -->
                    <div x-show="progress?.started_at" class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-gray-800 mb-2">⏰ Time Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="font-medium">Started:</span> 
                                <span x-text="formatDate(progress?.started_at)"></span>
                            </div>
                            <div>
                                <span class="font-medium">Last Update:</span> 
                                <span x-text="formatDate(progress?.updated_at)"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error Display -->
                    <div x-show="error" class="bg-red-50 border border-red-200 p-4 rounded-lg">
                        <h3 class="font-semibold text-red-800 mb-2">❌ Error</h3>
                        <p class="text-red-700" x-text="error"></p>
                    </div>
                </div>

                <!-- No Progress Message -->
                <div x-show="!progress && !isMonitoring && progressKey" class="text-center py-8">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No progress found for the specified key</p>
                        <p class="text-sm mt-2">Make sure the export process is running and the key is correct</p>
                    </div>
                </div>

                <!-- Instructions -->
                <div x-show="!progressKey" class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-2">💡 How to use</h3>
                    <ol class="text-blue-700 text-sm space-y-1 list-decimal list-inside">
                        <li>Start a PDF export using the console command</li>
                        <li>Copy the progress key displayed in the console</li>
                        <li>Paste the key above and click "Monitor"</li>
                        <li>Watch the real-time progress updates</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script>
        function progressMonitor() {
            return {
                progressKey: '',
                progress: null,
                error: null,
                isMonitoring: false,
                intervalId: null,

                init() {
                    // Check for progress key in URL params
                    const urlParams = new URLSearchParams(window.location.search);
                    const key = urlParams.get('key');
                    if (key) {
                        this.progressKey = key;
                        this.startMonitoring();
                    }
                },

                async startMonitoring() {
                    if (!this.progressKey) return;
                    
                    this.isMonitoring = true;
                    this.error = null;
                    
                    // Initial fetch
                    await this.fetchProgress();
                    
                    // Set up polling
                    this.intervalId = setInterval(() => {
                        this.fetchProgress();
                    }, 2000); // Poll every 2 seconds
                },

                async fetchProgress() {
                    try {
                        const response = await fetch(`/export-progress/${this.progressKey}`);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        this.progress = await response.json();
                        this.error = null;
                        
                        // Stop monitoring if completed
                        if (this.progress.percent >= 100 || this.progress.status === 'completed') {
                            this.stopMonitoring();
                        }
                        
                    } catch (err) {
                        this.error = err.message;
                        this.stopMonitoring();
                    }
                },

                stopMonitoring() {
                    this.isMonitoring = false;
                    if (this.intervalId) {
                        clearInterval(this.intervalId);
                        this.intervalId = null;
                    }
                },

                getStatusColor() {
                    const status = this.progress?.status?.toLowerCase();
                    switch (status) {
                        case 'completed':
                        case 'finished':
                            return 'text-green-600';
                        case 'processing':
                        case 'running':
                            return 'text-blue-600';
                        case 'error':
                        case 'failed':
                            return 'text-red-600';
                        default:
                            return 'text-gray-600';
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleString();
                }
            }
        }
    </script>
</body>
</html>