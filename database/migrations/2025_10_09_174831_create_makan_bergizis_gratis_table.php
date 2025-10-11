<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('makan_bergizis_gratis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabungan_id')->constrained('tabungans')->onDelete('cascade');
            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')->references('id_user')->on('profiles')->onDelete('cascade');
            $table->string('no_tabungan');
            $table->date('tanggal_pemberian');
            $table->json('data_rekening')->nullable();
            $table->json('data_nasabah')->nullable();
            $table->json('data_produk')->nullable();
            $table->json('data_transaksi_terakhir')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
            
            // Unique constraint: 1 record per hari per no_tabungan
            $table->unique(['no_tabungan', 'tanggal_pemberian'], 'unique_daily_record');
            $table->index('tanggal_pemberian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('makan_bergizis_gratis');
    }
};
