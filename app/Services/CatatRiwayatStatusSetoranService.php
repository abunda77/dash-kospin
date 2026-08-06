<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\RiwayatStatusSetoran;
use App\Models\SetoranTabungan;
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
        return RiwayatStatusSetoran::create([
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
    }
}
