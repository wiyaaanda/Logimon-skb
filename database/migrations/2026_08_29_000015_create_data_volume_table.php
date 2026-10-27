<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('data_volume', function (Blueprint $table) {
            $table->increments('id_volume');
            $table->unsignedInteger('id_pengiriman');
            $table->unsignedInteger('id_material')->nullable();
            $table->string('nama_material')->nullable();
            $table->string('satuan')->nullable();
            $table->date('tanggal')->nullable();
            $table->float('total_volume_kg')->nullable();
            $table->integer('jumlah_pengantaran')->nullable();
            $table->timestamps();

            $table->foreign('id_pengiriman')->references('id_pengiriman')->on('pengiriman')->onDelete('cascade');
            $table->foreign('id_material')->references('id_material')->on('material')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_volume');
    }
};
