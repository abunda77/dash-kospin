<?php

namespace App\Livewire\Filament\Pages\Birthday;

use App\Models\Profile;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class TodayTable extends \Filament\Tables\TableComponent implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        $today = Carbon::now()->timezone('Asia/Jakarta');

        return $table
            ->query(
                Profile::query()
                    ->whereMonth('birthday', $today->month)
                    ->whereDay('birthday', $today->day)
            )
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable(),
                TextColumn::make('birthday')
                    ->label('Birthday')
                    ->date()
                    ->sortable(),
                TextColumn::make('whatsapp')
                    ->label('Whatsapp')
                    ->url(fn ($record) => "https://wa.me/" . $record->whatsapp)
                    ->openUrlInNewTab(),
            ])
            ->heading('Ulang Tahun Hari Ini')
            ->defaultSort('first_name')
            ->paginated(false);
    }

    public function render()
    {
        return view('livewire.filament.pages.birthday.today-table');
    }
}
