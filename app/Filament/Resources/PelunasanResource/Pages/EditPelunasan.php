<?php

namespace App\Filament\Resources\PelunasanResource\Pages;

use App\Filament\Resources\PelunasanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Pinjaman;

class EditPelunasan extends EditRecord
{
    protected static string $resource = PelunasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Mengisi data nama lengkap berdasarkan profile_id yang tersedia
        if (isset($data['pinjaman_id'])) {
            $pinjaman = Pinjaman::find($data['pinjaman_id']);
            if ($pinjaman && $pinjaman->profile) {
                $data['nama_lengkap'] = $pinjaman->profile->first_name . ' ' . $pinjaman->profile->last_name;
                $data['jumlah_pinjaman'] = $pinjaman->jumlah_pinjaman;
            }
        }

        return $data;
    }
}
