<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\RiwayatStatusSetoran;
use App\Models\SetoranTabungan;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Request;

class CatatRiwayatStatusSetoranService
{
    public static function catat(
        SetoranTabungan $setoran,
        ?StatusSetoran $statusSebelumnya,
        StatusSetoran $statusBaru,
        ?string $diubahOlehType = null,
        ?int $diubahOlehId = null,
        ?string $catatan = null,
        ?array $metadata = null
    ): RiwayatStatusSetoran {
        $riwayat = RiwayatStatusSetoran::create([
            'setoran_id' => $setoran->id,
            'status_sebelumnya' => $statusSebelumnya?->value,
            'status_baru' => $statusBaru->value,
            'diubah_oleh_type' => $diubahOlehType,
            'diubah_oleh_id' => $diubahOlehId,
            'catatan' => $catatan,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);

        if ($statusSebelumnya !== null && $setoran->user !== null) {
            $setoran->user->notify(
                Notification::make()
                    ->title('Status Setoran Diperbarui')
                    ->body("Setoran #{$setoran->nomor_setoran} sekarang berstatus {$statusBaru->label()}.")
                    ->icon('heroicon-o-arrow-down-tray')
                    ->info()
                    ->toDatabase()
                    ->onConnection('sync')
            );
        }

        return $riwayat;
    }
}
