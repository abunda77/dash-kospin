<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('depositos', function (Blueprint $table) {
            $table->string('nama_bank')->nullable()->after('notes');
            $table->string('nomor_rekening_bank')->nullable()->after('nama_bank');
            $table->string('nama_pemilik_rekening_bank')->nullable()->after('nomor_rekening_bank');
        });
    }

    public function down()
    {
        Schema::table('depositos', function (Blueprint $table) {
            $table->dropColumn([
                'nama_bank',
                'nomor_rekening_bank',
                'nama_pemilik_rekening_bank'
            ]);
        });
    }
};
