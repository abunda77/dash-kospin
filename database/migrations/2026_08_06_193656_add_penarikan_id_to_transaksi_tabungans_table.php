<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_tabungans', function (Blueprint $table) {
            $table->foreignId('penarikan_id')
                ->nullable()
                ->unique()
                ->constrained('penarikan_tabungans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_tabungans', function (Blueprint $table) {
            $table->dropForeign(['penarikan_id']);
            $table->dropColumn('penarikan_id');
        });
    }
};
