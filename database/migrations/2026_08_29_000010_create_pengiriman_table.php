<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->increments('id_pengiriman');
            $table->unsignedInteger('id_jadwal')->nullable();
            $table->string('no_rec')->nullable();
            $table->time('jam_berangkat')->nullable();
            $table->float('vol_kg')->nullable();
            $table->float('total_volume_kg')->nullable();
            $table->float('timbangan_cv_sr')->nullable();
            $table->float('total_timbangan_cv_sr')->nullable();
            $table->enum('status', ['Terjadwal', 'Proses', 'Selesai', 'Pending'])->default('Terjadwal');
            $table->timestamps();

            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_pengiriman')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengiriman');
    }
};
