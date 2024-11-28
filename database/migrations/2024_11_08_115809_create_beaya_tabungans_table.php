<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bunga_beaya_tabungans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('persentase_bunga', 5, 2);
            $table->decimal('biaya_administrasi', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bunga_beaya_tabungans');
    }
};
