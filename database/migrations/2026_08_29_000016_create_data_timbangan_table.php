<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('data_timbangan', function (Blueprint $table) {
            $table->increments('id_timbangan');
            $table->unsignedInteger('id_pengiriman');
            $table->unsignedInteger('id_material')->nullable();
            $table->string('nama_material')->nullable();
            $table->string('satuan')->nullable();
            $table->date('tanggal')->nullable();
            $table->float('total_timbangan_cv_sr')->nullable();
            $table->integer('jumlah_pengantaran')->nullable();
            $table->timestamps();

            $table->foreign('id_pengiriman')->references('id_pengiriman')->on('pengiriman')->onDelete('cascade');
            $table->foreign('id_material')->references('id_material')->on('material')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_timbangan');
    }
};
