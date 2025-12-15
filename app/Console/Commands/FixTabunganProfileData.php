<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\Tabungan;
use Illuminate\Console\Command;

class FixTabunganProfileData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-tabungan-profile-data {--dry-run : Jalankan pengecekan tanpa melakukan perubahan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki data id_profile di tabel tabungans agar sesuai dengan id_user di tabel profiles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa data tabungan profile...');

        // Ambil semua id_profile unik dari tabel tabungans
        $tabunganProfiles = Tabungan::distinct()->pluck('id_profile')->toArray();

        // Ambil semua id_user yang valid dari tabel profiles
        $validUserIds = Profile::pluck('id_user')->toArray();

        $this->info('ID Profile di tabel tabungans: ' . implode(', ', $tabunganProfiles));
        $this->info('ID User valid di tabel profiles: ' . implode(', ', $validUserIds));

        // Cari id_profile yang tidak valid
        $invalidProfiles = array_diff($tabunganProfiles, $validUserIds);

        if (empty($invalidProfiles)) {
            $this->info('✅ Semua data id_profile sudah valid. Tidak ada yang perlu diperbaiki.');
            return;
        }

        $this->warn('⚠️  Ditemukan id_profile yang tidak valid: ' . implode(', ', $invalidProfiles));

        // Ambil id_user pertama yang valid sebagai fallback
        $fallbackUserId = reset($validUserIds);

        $this->info("ID User fallback yang akan digunakan: {$fallbackUserId}");

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run mode - hanya menampilkan apa yang akan diperbaiki:');

            foreach ($invalidProfiles as $invalidProfile) {
                $count = Tabungan::where('id_profile', $invalidProfile)->count();
                $this->line("  - {$count} tabungan dengan id_profile {$invalidProfile} akan diubah menjadi {$fallbackUserId}");
            }

            $this->info('Gunakan tanpa --dry-run untuk melakukan perbaikan sebenarnya.');
            return;
        }

        // Konfirmasi sebelum melakukan perubahan
        if (!$this->confirm('Apakah Anda yakin ingin memperbaiki data ini?', true)) {
            $this->info('Operasi dibatalkan.');
            return;
        }

        $this->info('🔧 Memulai perbaikan data...');

        $totalFixed = 0;
        foreach ($invalidProfiles as $invalidProfile) {
            $count = Tabungan::where('id_profile', $invalidProfile)->update(['id_profile' => $fallbackUserId]);
            $totalFixed += $count;
            $this->line("  ✓ {$count} tabungan dengan id_profile {$invalidProfile} diperbaiki");
        }

        $this->info("✅ Perbaikan selesai. Total {$totalFixed} data tabungan diperbaiki.");

        // Verifikasi akhir
        $remainingInvalid = Tabungan::whereNotIn('id_profile', $validUserIds)->count();
        if ($remainingInvalid > 0) {
            $this->error("⚠️  Masih ada {$remainingInvalid} data yang tidak valid. Periksa kembali.");
        } else {
            $this->info('✅ Semua data id_profile sekarang sudah valid.');
        }
    }
}
