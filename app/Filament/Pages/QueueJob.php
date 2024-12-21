<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Models\Job;
use Symfony\Component\Process\Process;
use Livewire\Attributes\Reactive;

class QueueJob extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.queue-job';
    protected static ?string $navigationGroup = 'Settings';

    public bool $isProcessing = false;
    public $output = '';

    public function getTableQuery()
    {
        return Job::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id'),
            TextColumn::make('queue'),
            TextColumn::make('payload')->limit(50),
            TextColumn::make('attempts'),
            TextColumn::make('created_at'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('processQueue')
                ->label('Run Queue Worker')
                ->action(function () {
                    $this->startQueueWorker();
                })
                ->disabled(fn() => $this->isProcessing),

            Action::make('stopQueue')
                ->label('Stop Queue Worker')
                ->color('danger')
                ->action(function () {
                    $this->stopQueueWorker();
                })
                ->disabled(fn() => !$this->isProcessing),
        ];
    }

    public function startQueueWorker()
    {
        $this->isProcessing = true;
        $this->output = '';

        try {
            // Jalankan queue worker secara langsung
            $this->output .= "Starting queue worker...\n";

            $exitCode = \Illuminate\Support\Facades\Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--queue' => 'default',
                '--tries' => 3
            ]);

            $this->output .= \Illuminate\Support\Facades\Artisan::output();

            if ($exitCode === 0) {
                $this->output .= "\nQueue processing completed successfully.";
            } else {
                $this->output .= "\nQueue processing failed with exit code: " . $exitCode;
            }
        } catch (\Exception $e) {
            $this->output .= "\nError: " . $e->getMessage();
        } finally {
            $this->isProcessing = false;
            $this->dispatch('queue-completed');
        }
    }

    public function stopQueueWorker()
    {
        if ($this->isProcessing) {
            try {
                \Illuminate\Support\Facades\Artisan::call('queue:restart');
                $this->output .= "\nQueue worker stopped.";
            } catch (\Exception $e) {
                $this->output .= "\nError stopping queue: " . $e->getMessage();
            }

            $this->isProcessing = false;
            $this->dispatch('queue-stopped');
        }
    }
}
