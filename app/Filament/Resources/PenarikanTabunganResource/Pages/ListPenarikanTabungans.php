<?php

namespace App\Filament\Resources\PenarikanTabunganResource\Pages;

use App\Enums\StatusPenarikan;
use App\Filament\Resources\PenarikanTabunganResource;
use App\Models\PenarikanTabungan;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPenarikanTabungans extends ListRecords
{
    protected static string $resource = PenarikanTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $perluTindakan = [
            StatusPenarikan::MENUNGGU_VERIFIKASI->value,
            StatusPenarikan::SEDANG_DIPERIKSA->value,
            StatusPenarikan::PERLU_REVISI->value,
        ];

        $selesai = [
            StatusPenarikan::DISETUJUI->value,
            StatusPenarikan::SELESAI->value,
        ];

        $ditolakBatal = [
            StatusPenarikan::DITOLAK->value,
            StatusPenarikan::DIBATALKAN->value,
        ];

        return [
            'perlu_tindakan' => Tab::make('Perlu Tindakan')
                ->icon('heroicon-o-exclamation-circle')
                ->badge(PenarikanTabungan::whereIn('status', $perluTindakan)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $perluTindakan)),

            'selesai' => Tab::make('Selesai')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $selesai)),

            'ditolak_batal' => Tab::make('Ditolak / Batal')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $ditolakBatal)),

            'semua' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'perlu_tindakan';
    }
}
