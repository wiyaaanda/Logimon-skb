<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transporter', function (Blueprint $table) {
            $table->increments('id_transporter');
            $table->string('nama_transporter');
            $table->string('no_telp_transporter')->nullable();
            $table->string('tempat_transporter')->nullable();
            $table->string('email_transporter')->nullable();
            $table->string('pic_transporter')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transporter');
    }
};
