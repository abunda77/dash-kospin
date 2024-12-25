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
        $this->progress = 0;
        $this->results = [];

        // Test response time
        $startTime = microtime(true);
        $response = Http::get(config('app.url'));
        $endTime = microtime(true);

        $this->results['responseTime'] = round(($endTime - $startTime) * 1000, 2); // dalam milliseconds
        $this->progress = 50;

        // Test requests per second
        $startTime = microtime(true);
        $requests = 0;
        $duration = 5; // durasi test dalam detik

        while (microtime(true) - $startTime < $duration) {
            Http::get(config('app.url'));
            $requests++;
            $this->progress = 50 + (((microtime(true) - $startTime) / $duration) * 50);
        }

        $this->results['requestsPerSecond'] = round($requests / $duration, 2);
        $this->progress = 100;
        $this->isTestingSpeed = false;
    }
}
