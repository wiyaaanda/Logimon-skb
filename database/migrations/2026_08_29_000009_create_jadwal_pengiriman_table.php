<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jadwal_pengiriman', function (Blueprint $table) {
            $table->increments('id_jadwal');
            $table->date('tanggal');
            $table->string('hari')->nullable();

            $table->unsignedInteger('id_driver')->nullable();
            $table->unsignedInteger('id_kendaraan')->nullable();
            $table->unsignedInteger('id_transporter')->nullable();
            $table->unsignedInteger('id_asal')->nullable();
            $table->unsignedInteger('id_tujuan')->nullable();
            $table->unsignedInteger('id_material')->nullable();
            $table->string('code_material')->nullable();

            $table->string('no_pol')->nullable();

            $table->enum('status', ['Terjadwal', 'Proses', 'Selesai', 'Pending'])->default('Terjadwal');
            $table->enum('tingkat_urgensi', ['Mendesak', 'Prioritas'])->nullable();

            $table->float('nilai_bobot')->default(0);
            $table->integer('progress_tujuan_persen')->default(0);
            $table->timestamps();

            $table->foreign('id_driver')->references('id_driver')->on('driver')->onDelete('set null');
            $table->foreign('id_kendaraan')->references('id_kendaraan')->on('kendaraan')->onDelete('set null');
            $table->foreign('id_transporter')->references('id_transporter')->on('transporter')->onDelete('set null');
            $table->foreign('id_asal')->references('id_asal')->on('asal')->onDelete('set null');
            $table->foreign('id_tujuan')->references('id_tujuan')->on('tujuan')->onDelete('set null');
            $table->foreign('id_material')->references('id_material')->on('material')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_pengiriman');
    }
};
