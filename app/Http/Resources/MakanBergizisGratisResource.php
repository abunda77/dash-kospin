<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MakanBergizisGratisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_tabungan' => $this->no_tabungan,
            'tanggal_pemberian' => $this->tanggal_pemberian->format('d/m/Y'),
            'tanggal_pemberian_iso' => $this->tanggal_pemberian->toISOString(),
            'rekening' => $this->data_rekening,
            'nasabah' => $this->data_nasabah,
            'produk_detail' => $this->data_produk,
            'transaksi_terakhir' => $this->data_transaksi_terakhir,
            'metadata' => [
                'scanned_at' => $this->scanned_at->toISOString(),
                'scanned_at_formatted' => $this->scanned_at->format('d/m/Y H:i:s'),
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
            ]
        ];
    }
}
