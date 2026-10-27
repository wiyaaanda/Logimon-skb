<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver', function (Blueprint $table) {
            $table->increments('id_driver');
            $table->string('nama_driver');
            $table->string('no_telp_driver')->nullable();
            $table->string('tempat_driver')->nullable();
            $table->date('tanggal_lahir_driver')->nullable();
            $table->string('sim_driver')->nullable();
            $table->string('status_aktif_driver')->nullable();
            $table->unsignedInteger('id_transporter')->nullable();
            $table->timestamps();

            $table->foreign('id_transporter')->references('id_transporter')->on('transporter')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver');
    }
};
