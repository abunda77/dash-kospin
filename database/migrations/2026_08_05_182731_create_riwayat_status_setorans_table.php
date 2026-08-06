<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setoran_id')->constrained('setoran_tabungans')->cascadeOnDelete();
            $table->string('status_sebelumnya')->nullable();
            $table->string('status_baru');
            $table->string('diubah_oleh_type')->nullable();
            $table->unsignedBigInteger('diubah_oleh_id')->nullable();
            $table->text('catatan')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['diubah_oleh_type', 'diubah_oleh_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_setorans');
    }
};
