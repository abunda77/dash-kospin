<?php

namespace App\Livewire\Filament\Pages\Birthday;

use Carbon\Carbon;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\BirthdayLog;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class BirthdayLogTable extends \Filament\Tables\TableComponent implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

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
                    ->label('First Name')
                    ->sortable(),
                TextColumn::make('profile.last_name')
                    ->label('Last Name')
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
                    ->color(fn (string $state): string =>
                        $state ? 'success' : 'danger'
                    )
                    ->formatStateUsing(fn (bool $state): string =>
                        $state ? 'Sent' : 'Not Sent'
                    ),
            ])
            ->heading('Log Pengiriman Hari Ini')
            ->defaultSort('date_sent', 'desc')
            ->paginated(false);
    }

    public function render()
    {
        return view('livewire.filament.pages.birthday.birthdaylog-table');
    }
}
