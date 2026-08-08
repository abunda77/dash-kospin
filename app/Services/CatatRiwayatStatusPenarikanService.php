<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\RiwayatStatusPenarikan;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Request;

class CatatRiwayatStatusPenarikanService
{
    public static function catat(
        PenarikanTabungan $penarikan,
        ?StatusPenarikan $statusSebelumnya,
        StatusPenarikan $statusBaru,
        ?string $diubahOlehType = null,
        ?int $diubahOlehId = null,
        ?string $catatan = null,
        ?array $metadata = null
    ): RiwayatStatusPenarikan {
        $riwayat = RiwayatStatusPenarikan::create([
            'penarikan_id' => $penarikan->id,
            'status_sebelumnya' => $statusSebelumnya?->value,
            'status_baru' => $statusBaru->value,
            'diubah_oleh_type' => $diubahOlehType,
            'diubah_oleh_id' => $diubahOlehId,
            'catatan' => $catatan,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        if ($statusSebelumnya !== null && $penarikan->user !== null) {
            $penarikan->user->notify(
                Notification::make()
                    ->title('Status Penarikan Diperbarui')
                    ->body("Penarikan #{$penarikan->nomor_penarikan} sekarang berstatus {$statusBaru->label()}.")
                    ->icon('heroicon-o-arrow-up-tray')
                    ->info()
                    ->toDatabase()
                    ->onConnection('sync')
            );
        }

        return $riwayat;
    }
}
