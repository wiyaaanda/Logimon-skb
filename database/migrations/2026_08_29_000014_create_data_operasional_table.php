<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('data_operasional', function (Blueprint $table) {
            $table->increments('id_operasional');
            $table->unsignedInteger('id_pengiriman');
            $table->unsignedInteger('id_material')->nullable();
            $table->string('nama_material')->nullable();
            $table->unsignedInteger('id_tujuan')->nullable();
            $table->string('tujuan')->nullable();
            $table->string('no_rec')->nullable();
            $table->time('jam_berangkat')->nullable();
            $table->timestamps();

            $table->foreign('id_pengiriman')->references('id_pengiriman')->on('pengiriman')->onDelete('cascade');
            $table->foreign('id_material')->references('id_material')->on('material')->onDelete('set null');
            $table->foreign('id_tujuan')->references('id_tujuan')->on('tujuan')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_operasional');
    }
};
