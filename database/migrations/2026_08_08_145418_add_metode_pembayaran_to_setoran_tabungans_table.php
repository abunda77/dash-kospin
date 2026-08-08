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
        Schema::table('setoran_tabungans', function (Blueprint $table) {
            $table->string('metode_pembayaran')
                ->default('qris')
                ->after('jumlah_bayar')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setoran_tabungans', function (Blueprint $table) {
            $table->dropIndex(['metode_pembayaran']);
            $table->dropColumn('metode_pembayaran');
        });
    }
};
