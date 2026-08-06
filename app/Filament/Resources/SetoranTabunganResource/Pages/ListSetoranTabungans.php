<?php

namespace App\Filament\Resources\SetoranTabunganResource\Pages;

use App\Enums\StatusSetoran;
use App\Filament\Resources\SetoranTabunganResource;
use App\Models\SetoranTabungan;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSetoranTabungans extends ListRecords
{
    protected static string $resource = SetoranTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $perluTindakan = [
            StatusSetoran::MENUNGGU_VERIFIKASI->value,
            StatusSetoran::SEDANG_DIPERIKSA->value,
            StatusSetoran::PERLU_REVISI->value,
        ];

        $selesai = [
            StatusSetoran::DISETUJUI->value,
            StatusSetoran::SELESAI->value,
        ];

        $ditolakBatal = [
            StatusSetoran::DITOLAK->value,
            StatusSetoran::DIBATALKAN->value,
            StatusSetoran::KEDALUWARSA->value,
        ];

        return [
            'perlu_tindakan' => Tab::make('Perlu Tindakan')
                ->icon('heroicon-o-exclamation-circle')
                ->badge(SetoranTabungan::whereIn('status', $perluTindakan)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $perluTindakan)),

            'menunggu_pembayaran' => Tab::make('Menunggu Pembayaran')
                ->icon('heroicon-o-clock')
                ->badge(SetoranTabungan::where('status', StatusSetoran::MENUNGGU_PEMBAYARAN)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusSetoran::MENUNGGU_PEMBAYARAN)),

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
