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
use Livewire\Component;

class TomorrowTable extends \Filament\Tables\TableComponent implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        $tomorrow = Carbon::tomorrow()->timezone('Asia/Jakarta');

        return $table
            ->query(
                Profile::query()
                    ->whereMonth('birthday', $tomorrow->month)
                    ->whereDay('birthday', $tomorrow->day)
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
            ->heading('Ulang Tahun Besok')
            ->defaultSort('first_name')
            ->paginated(false);
    }

    public function render()
    {
        return view('livewire.filament.pages.birthday.tomorrow-table');
    }
}
