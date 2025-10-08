<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TabunganScanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rekening' => [
                'no_tabungan' => $this->no_tabungan,
                'produk' => $this->produkTabungan->nama_produk ?? 'N/A',
                'saldo' => $this->saldo,
                'saldo_formatted' => format_rupiah($this->saldo),
                'status' => $this->status_rekening,
                'tanggal_buka' => $this->tanggal_buka?->format('d/m/Y'),
                'tanggal_buka_iso' => $this->tanggal_buka?->toISOString(),
            ],
            'nasabah' => [
                'nama_lengkap' => $this->profile->first_name . ' ' . $this->profile->last_name,
                'first_name' => $this->profile->first_name,
                'last_name' => $this->profile->last_name,
                'phone' => $this->profile->phone,
                'email' => $this->profile->email,
                'whatsapp' => $this->profile->whatsapp,
                'address' => $this->profile->address,
            ],
            'produk_detail' => [
                'id' => $this->produkTabungan->id ?? null,
                'nama' => $this->produkTabungan->nama_produk ?? 'N/A',
                'keterangan' => $this->produkTabungan->keterangan ?? null,
            ],
            'metadata' => [
                'scanned_at' => now()->toISOString(),
                'scanned_at_formatted' => now()->format('d/m/Y H:i:s'),
            ]
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Tabungan data retrieved successfully',
        ];
    }
}
