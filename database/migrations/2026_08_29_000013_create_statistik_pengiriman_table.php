<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('statistik_pengiriman', function (Blueprint $table) {
            $table->increments('id_statistik');
            $table->unsignedInteger('id_pengiriman')->nullable();
            $table->unsignedInteger('id_driver')->nullable();
            $table->unsignedInteger('id_material')->nullable();
            $table->unsignedInteger('id_transporter')->nullable();

            $table->string('hari')->nullable();
            $table->string('bulan')->nullable();
            $table->string('tahun')->nullable();

            $table->integer('total_pengiriman')->default(0);
            $table->integer('driver_aktif')->default(0);
            $table->string('top_material')->nullable();
            $table->float('tren_volume_bulanan')->nullable();
            $table->integer('jml_selesai')->default(0);
            $table->integer('jml_pending')->default(0);
            $table->integer('jml_proses')->default(0);
            $table->integer('jml_mendesak')->default(0);
            $table->integer('jml_prioritas')->default(0);
            $table->float('on_time_delivery_rate')->nullable();
            $table->timestamps();

            $table->foreign('id_pengiriman')->references('id_pengiriman')->on('pengiriman')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('statistik_pengiriman');
    }
};
