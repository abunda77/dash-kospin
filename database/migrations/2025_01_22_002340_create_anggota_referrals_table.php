<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_referral', function (Blueprint $table) {
            $table->id('id_referral');
            $table->string('kode_referral', 20)->unique();
            $table->string('nama', 100);
            $table->enum('status_referral', ['Freelance', 'Marketing', 'Staff']);
            $table->string('no_rekening', 30);
            $table->string('bank', 50);
            $table->string('atas_nama_bank', 100);
            $table->string('email', 100)->unique();
            $table->string('no_hp', 20);
            $table->datetime('tanggal_bergabung');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_referral');
    }
};
