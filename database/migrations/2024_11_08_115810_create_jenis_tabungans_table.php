<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jenis_tabungans', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['reguler', 'berjangka', 'deposito']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jenis_tabungans');
    }
};
