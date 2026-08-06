<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_tabungans', function (Blueprint $table) {
            $table->foreignId('setoran_id')
                ->nullable()
                ->unique()
                ->constrained('setoran_tabungans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_tabungans', function (Blueprint $table) {
            $table->dropForeign(['setoran_id']);
            $table->dropColumn('setoran_id');
        });
    }
};
