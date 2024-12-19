<?php

namespace App\Livewire\Filament\Pages\Birthday;

use Carbon\Carbon;
use Filament\Tables\Table;
use App\Models\BirthdayLog;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;

class BirthdayLogTable extends \Filament\Tables\TableComponent
{
    use InteractsWithTable;

    // Tambahkan listeners
    protected $listeners = ['birthday-log-updated' => '$refresh'];

    // Tambahkan properti $componentName
    //protected static string $componentName = 'birthday-log-table';

    public function mount()
    {
        Log::info('BirthdayLogTable Component Loaded');
        Log::info('Current Directory: ' . __DIR__);
        Log::info('Current Class: ' . get_class($this));
    }

    public function table(Table $table): Table
    {
        $today = Carbon::now()->timezone('Asia/Jakarta');

        return $table
            ->query(
                BirthdayLog::query()
                    ->with('profile')
                    ->whereDate('date_sent', $today->toDateString())
            )
            ->columns([
                TextColumn::make('profile.first_name')
                    ->label('Full Name')
                    ->formatStateUsing(fn ($record) => $record->profile->first_name . ' ' . $record->profile->last_name)
                    ->sortable(),
                TextColumn::make('profile.birthday')
                    ->label('Birthday')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_sent')
                    ->label('Sent Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status_sent')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sent' : 'Not Sent'),
            ])
            ->heading('Log Pengiriman Hari Ini')
            ->defaultSort('date_sent', 'desc')
            ->paginated(false);
    }
}

// end of file
