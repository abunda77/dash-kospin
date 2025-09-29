<?php

namespace App\Filament\Pages;

use App\Models\Profile;
use App\Models\TransaksiTabungan;
use App\Models\TransaksiPinjaman;
use App\Models\Tabungan;
use App\Models\Pinjaman;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Actions\Action as PageAction;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class LaporanNasabahAktif extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static string $view = 'filament.pages.laporan-nasabah-aktif';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Nasabah Aktif';
    protected static ?string $title = 'Laporan Nasabah Aktif';

    public $dateFrom;
    public $dateTo;
    public $transactionType = 'all'; // all, savings, loans

    public function mount(): void
    {
        // Set default date range to last 90 days
        $this->dateTo = Carbon::now()->toDateString();
        $this->dateFrom = Carbon::now()->subDays(90)->toDateString();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('dateFrom')
                    ->label('Tanggal Mulai')
                    ->default(Carbon::now()->subDays(90))
                    ->required(),
                DatePicker::make('dateTo')
                    ->label('Tanggal Selesai')
                    ->default(Carbon::now())
                    ->required(),
                Select::make('transactionType')
                    ->label('Jenis Transaksi')
                    ->options([
                        'all' => 'Semua Transaksi',
                        'savings' => 'Transaksi Tabungan',
                        'loans' => 'Transaksi Pinjaman',
                    ])
                    ->default('all')
                    ->required(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getActiveCustomersQuery())
            ->columns([
                TextColumn::make('no_identity')
                    ->label('No. Identitas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name'])
                    ->getStateUsing(fn($record) => trim($record->first_name . ' ' . $record->last_name)),
                TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable(),
                TextColumn::make('last_savings_transaction')
                    ->label('Transaksi Tabungan Terakhir')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('last_loan_transaction')
                    ->label('Transaksi Pinjaman Terakhir')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('total_savings_transactions')
                    ->label('Jumlah Transaksi Tabungan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_loan_transactions')
                    ->label('Jumlah Transaksi Pinjaman')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_transactions')
                    ->label('Total Transaksi')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // Filters sudah dihandle di form dan query
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (Profile $record): string =>
                        '/admin/profiles/' . $record->id_user)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('total_transactions', 'desc');
    }

    protected function getActiveCustomersQuery(): Builder
    {
        $dateFrom = $this->dateFrom ?: Carbon::now()->subDays(90)->toDateString();
        $dateTo = $this->dateTo ?: Carbon::now()->toDateString();

        // Subquery untuk mendapatkan transaksi tabungan
        $savingsSubquery = DB::table('transaksi_tabungans as st')
            ->join('tabungans as t', 'st.id_tabungan', '=', 't.id')
            ->select([
                't.id_profile',
                DB::raw('MAX(st.tanggal_transaksi) as last_savings_transaction'),
                DB::raw('COUNT(st.id) as total_savings_transactions')
            ])
            ->whereBetween('st.tanggal_transaksi', [$dateFrom, $dateTo])
            ->groupBy('t.id_profile');

        // Subquery untuk mendapatkan transaksi pinjaman
        $loansSubquery = DB::table('transaksi_pinjamans as pt')
            ->join('pinjamans as p', 'pt.pinjaman_id', '=', 'p.id_pinjaman')
            ->select([
                'p.profile_id',
                DB::raw('MAX(pt.tanggal_pembayaran) as last_loan_transaction'),
                DB::raw('COUNT(pt.id) as total_loan_transactions')
            ])
            ->whereBetween('pt.tanggal_pembayaran', [$dateFrom, $dateTo])
            ->groupBy('p.profile_id');

        return Profile::query()
            ->select([
                'profiles.*',
                'savings_data.last_savings_transaction',
                DB::raw('COALESCE(savings_data.total_savings_transactions, 0) as total_savings_transactions'),
                'loans_data.last_loan_transaction',
                DB::raw('COALESCE(loans_data.total_loan_transactions, 0) as total_loan_transactions'),
                DB::raw('(COALESCE(savings_data.total_savings_transactions, 0) + COALESCE(loans_data.total_loan_transactions, 0)) as total_transactions')
            ])
            ->leftJoinSub($savingsSubquery, 'savings_data', function ($join) {
                $join->on('profiles.id_user', '=', 'savings_data.id_profile');
            })
            ->leftJoinSub($loansSubquery, 'loans_data', function ($join) {
                $join->on('profiles.id_user', '=', 'loans_data.profile_id');
            })
            ->where(function ($query) {
                $query->whereNotNull('savings_data.id_profile')
                      ->orWhereNotNull('loans_data.profile_id');
            })
            ->when($this->transactionType === 'savings', function ($query) {
                return $query->whereNotNull('savings_data.id_profile');
            })
            ->when($this->transactionType === 'loans', function ($query) {
                return $query->whereNotNull('loans_data.profile_id');
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('exportToPdf'),
            PageAction::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action('refreshData'),
        ];
    }

    public function exportToPdf()
    {
        $data = $this->getActiveCustomersQuery()->get();
        $dateFrom = $this->dateFrom ?: Carbon::now()->subDays(90)->toDateString();
        $dateTo = $this->dateTo ?: Carbon::now()->toDateString();

        $pdf = Pdf::loadView('pdf.laporan-nasabah-aktif', [
            'data' => $data,
            'dateFrom' => Carbon::parse($dateFrom)->format('d/m/Y'),
            'dateTo' => Carbon::parse($dateTo)->format('d/m/Y'),
            'transactionType' => $this->transactionType,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ]);

        $filename = 'laporan-nasabah-aktif-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';

        return Response::streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $filename);
    }

    public function refreshData()
    {
        $this->resetTable();
        $this->dispatch('$refresh');

        \Filament\Notifications\Notification::make()
            ->title('Data berhasil diperbarui')
            ->success()
            ->send();
    }

    public function getStats(): array
    {
        $query = $this->getActiveCustomersQuery();
        $totalActiveCustomers = $query->count();

        $dateFrom = $this->dateFrom ?: Carbon::now()->subDays(90)->toDateString();
        $dateTo = $this->dateTo ?: Carbon::now()->toDateString();

        $savingsTransactionsCount = TransaksiTabungan::whereBetween('tanggal_transaksi', [$dateFrom, $dateTo])->count();
        $loanTransactionsCount = TransaksiPinjaman::whereBetween('tanggal_pembayaran', [$dateFrom, $dateTo])->count();

        return [
            [
                'label' => 'Total Nasabah Aktif',
                'value' => $totalActiveCustomers,
                'description' => 'Nasabah dengan transaksi dalam periode ini',
                'icon' => 'heroicon-o-users',
                'color' => 'success',
            ],
            [
                'label' => 'Transaksi Tabungan',
                'value' => $savingsTransactionsCount,
                'description' => 'Total transaksi tabungan dalam periode',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'info',
            ],
            [
                'label' => 'Transaksi Pinjaman',
                'value' => $loanTransactionsCount,
                'description' => 'Total transaksi pinjaman dalam periode',
                'icon' => 'heroicon-o-credit-card',
                'color' => 'warning',
            ],
        ];
    }
}