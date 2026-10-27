<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('status_log', function (Blueprint $table) {
            $table->increments('id_log');
            $table->unsignedInteger('id_pengiriman');
            $table->unsignedInteger('id_user')->nullable();
            $table->string('driver')->nullable();
            $table->string('username')->nullable();
            $table->string('dari')->nullable();
            $table->string('tujuan')->nullable();
            $table->string('material')->nullable();
            $table->dateTime('waktu_pending')->nullable();
            $table->dateTime('waktu_penyelesaian')->nullable();
            $table->enum('status_update', ['Terjadwal', 'Proses', 'Selesai', 'Pending'])->default('Pending');
            $table->text('keterangan_log')->nullable();
            $table->timestamps();

            $table->foreign('id_pengiriman')->references('id_pengiriman')->on('pengiriman')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('status_log');
    }
};
