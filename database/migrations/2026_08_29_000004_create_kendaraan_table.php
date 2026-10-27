<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->increments('id_kendaraan');
            $table->string('no_pol');
            $table->string('jenis_kendaraan')->nullable();
            $table->string('code_kendaraan')->nullable();
            $table->unsignedInteger('id_transporter')->nullable();
            $table->timestamps();

            $table->foreign('id_transporter')->references('id_transporter')->on('transporter')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kendaraan');
    }
};
