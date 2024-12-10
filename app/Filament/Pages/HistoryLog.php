<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class HistoryLog extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.history-log';
    protected static ?string $navigationLabel = 'Activity Logs';
    protected static ?string $title = 'Activity Logs';

    // Tambahkan navigation group jika diperlukan
    protected static ?string $navigationGroup = 'Setting';

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::query())
            ->columns([
                TextColumn::make('admin.name')
                    ->label('Admin')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('Dari Tanggal'),
                        DatePicker::make('to_date')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->defaultSort('created_at', 'desc');
    }
}
