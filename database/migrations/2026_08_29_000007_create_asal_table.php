<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asal', function (Blueprint $table) {
            $table->increments('id_asal');
            $table->string('nama_asal');
            $table->string('kota_asal')->nullable();
            $table->string('provensi_asal')->nullable();
            $table->string('no_telp_asal')->nullable();
            $table->string('link_map_asal')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asal');
    }
};
