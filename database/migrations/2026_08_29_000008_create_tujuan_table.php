<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tujuan', function (Blueprint $table) {
            $table->increments('id_tujuan');
            $table->string('nama_tujuan');
            $table->string('kota_tujuan')->nullable();
            $table->string('provensi_tujuan')->nullable();
            $table->string('no_telp_tujuan')->nullable();
            $table->string('link_map_tujuan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tujuan');
    }
};
