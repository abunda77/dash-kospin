<?php

namespace App\Filament\Resources\CicilanEmasResource\Pages;

use App\Filament\Resources\CicilanEmasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCicilanEmas extends CreateRecord
{
    protected static string $resource = CicilanEmasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan no_transaksi selalu terisi
        if (empty($data['no_transaksi'])) {
            $data['no_transaksi'] = 'CMS' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        // Pastikan setoran_awal dan biaya_admin dihitung dengan benar
        if (isset($data['total_harga'])) {
            $data['setoran_awal'] = $data['total_harga'] * 0.05; // 5% dari total harga
            $data['biaya_admin'] = $data['total_harga'] * 0.005; // 0.5% dari total harga
        }

        return $data;
    }
}
