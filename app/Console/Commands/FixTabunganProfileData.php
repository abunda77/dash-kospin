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
    protected $signature = 'app:fix-tabungan-profile-data {--dry-run : Jalankan pengecekan tanpa melakukan perubahan} {--force : Jalankan perbaikan tanpa konfirmasi}';

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

        // Mapping berdasarkan profiles.id ke profiles.id_user
        // Jika id_profile = 1, cari profile dengan id = 1, ambil id_user nya
        $mapping = [];
        foreach ($invalidProfiles as $invalidProfile) {
            // Coba cari profile dengan id = invalidProfile (bukan id_user)
            $profile = Profile::where('id', $invalidProfile)->first();
            if ($profile) {
                $mapping[$invalidProfile] = $profile->id_user; // Ambil id_user yang benar
            } else {
                // Jika tidak ada profile dengan id = invalidProfile, gunakan fallback
                $mapping[$invalidProfile] = reset($validUserIds);
            }
        }

        $this->info("Mapping perbaikan:");
        foreach ($mapping as $from => $to) {
            $this->line("  {$from} → {$to}");
        }

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run mode - hanya menampilkan apa yang akan diperbaiki:');

            foreach ($mapping as $from => $to) {
                $count = Tabungan::where('id_profile', $from)->count();
                $this->line("  - {$count} tabungan dengan id_profile {$from} akan diubah menjadi {$to}");
            }

            $this->info('Gunakan tanpa --dry-run untuk melakukan perbaikan sebenarnya.');
            return;
        }

        // Konfirmasi sebelum melakukan perubahan
        $confirmed = $this->option('force') || $this->confirm('Apakah Anda yakin ingin memperbaiki data ini?', true);
        if (!$confirmed) {
            $this->info('Operasi dibatalkan.');
            return;
        }

        $this->info('🔧 Memulai perbaikan data...');

        $totalFixed = 0;
        foreach ($mapping as $from => $to) {
            $count = Tabungan::where('id_profile', $from)->update(['id_profile' => $to]);
            $totalFixed += $count;
            $this->line("  ✓ {$count} tabungan dengan id_profile {$from} diperbaiki menjadi {$to}");
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
