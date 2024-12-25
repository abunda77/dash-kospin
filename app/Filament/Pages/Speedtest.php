<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class Speedtest extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Speedtest';

    protected static ?string $title = 'Speedtest';


    protected static string $view = 'filament.pages.speedtest';

    public $isTestingSpeed = false;
    public $progress = 0;
    public $results = [];

    public function testSpeed()
    {
        $this->isTestingSpeed = true;
        $this->results = []; // Reset hasil sebelumnya

        $startTime = microtime(true);
        $requestCount = 0;

        for ($i = 0; $i <= 100; $i += 10) {
            $this->progress = $i;
            $requestCount++;
            usleep(500000); // Delay 0.5 detik
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // Konversi ke milliseconds

        $this->results = [
            'responseTime' => round($totalTime, 2),
            'requestsPerSecond' => round($requestCount / ($totalTime / 1000), 2)
        ];

        $this->isTestingSpeed = false;
        $this->dispatch('progressUpdated');
    }
}
