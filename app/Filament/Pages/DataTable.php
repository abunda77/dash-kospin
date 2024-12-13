<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class DataTable extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Data Table';
    protected static ?string $title = 'Data Table';

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    protected static string $view = 'filament.pages.data-table';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->defaultSort('table_name', 'asc');
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('table_name')
                ->label('Nama Tabel')
                ->searchable()
                ->sortable(),
            TextColumn::make('field_count')
                ->label('Jumlah Field')
                ->sortable(),
            TextColumn::make('record_count')
                ->label('Jumlah Data')
                ->sortable(),
        ];
    }

    protected function getTableQuery()
    {
        // Membuat query builder
        return \App\Models\TableInfo::query()
            ->setQuery(
                \App\Models\TableInfo::query()->fromSub(function ($query) {
                    $tables = [];
                    $dbTables = DB::select('SHOW TABLES');

                    foreach ($dbTables as $table) {
                        $tableName = current((array)$table);
                        $fieldCount = count(Schema::getColumnListing($tableName));
                        $recordCount = DB::table($tableName)->count();

                        $tables[] = [
                            'table_name' => $tableName,
                            'field_count' => $fieldCount,
                            'record_count' => $recordCount,
                        ];
                    }

                    if (empty($tables)) {
                        return $query->fromRaw('(SELECT NULL as table_name, NULL as field_count, NULL as record_count) as empty_result WHERE 1=0');
                    }

                    $query->fromRaw('(SELECT ? as table_name, ? as field_count, ? as record_count) as t0',
                        array_values($tables[0]));

                    foreach (array_slice($tables, 1) as $index => $table) {
                        $query->unionAll(
                            DB::query()->fromRaw('(SELECT ? as table_name, ? as field_count, ? as record_count) as t' . ($index + 1),
                                array_values($table))
                        );
                    }

                    return $query;
                }, 'table_info')
                ->getQuery()
            );
    }

    public function getTableRecordKey($record): string
    {
        return $record->table_name;
    }
}
