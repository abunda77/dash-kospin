<?php

namespace Tests\Feature;

use App\Filament\Pages\CetakSertifikatSimpanan;
use App\Models\BeayaTabungan;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\Tabungan;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Tests\TestCase;

class CetakSertifikatSimpananTest extends TestCase
{
    public function test_halaman_menggunakan_page_shield(): void
    {
        $traits = class_uses_recursive(CetakSertifikatSimpanan::class);

        $this->assertContains(HasPageShield::class, $traits);
    }

    public function test_template_sertifikat_menampilkan_data_rekening(): void
    {
        $beaya = new BeayaTabungan([
            'persentase_bunga' => 5.50,
        ]);
        $produk = new ProdukTabungan([
            'nama_produk' => 'Simpanan Berjangka',
        ]);
        $produk->setRelation('beayaTabungan', $beaya);
        $profile = new Profile([
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'address' => 'Jalan Melati 10',
            'phone' => '081234567890',
        ]);
        $tabungan = new Tabungan([
            'no_tabungan' => 'TAB-0001',
            'saldo' => 1500000,
            'tanggal_buka_rekening' => '2026-07-16',
        ]);
        $tabungan->setRelation('produkTabungan', $produk);
        $tabungan->setRelation('profile', $profile);

        $jangkaWaktu = 12;
        $akhirKontrak = Carbon::parse($tabungan->tanggal_buka_rekening)->addMonths($jangkaWaktu);

        $html = view('pdf.sertifikat-simpanan-a5', [
            'tabungan' => $tabungan,
            'jangkaWaktu' => $jangkaWaktu,
            'akhirKontrak' => $akhirKontrak,
            'bankAtasNama' => 'Budi Santoso',
            'bankNoRekening' => '1234567890',
            'bankNamaBank' => 'Bank BRI',
        ])->render();

        $this->assertStringContainsString('Sertifikat Simpanan Berjangka', $html);
        $this->assertStringContainsString('TAB-0001', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
        $this->assertStringContainsString('Jalan Melati 10', $html);
        $this->assertStringContainsString('081234567890', $html);
        $this->assertStringContainsString('Rp. 1.500.000', $html);
        $this->assertStringContainsString('12 Bulan', $html);
        $this->assertStringContainsString('5.5% p.a', $html);
        $this->assertStringContainsString('1234567890', $html);
        $this->assertStringContainsString('Bank BRI', $html);
    }
}
