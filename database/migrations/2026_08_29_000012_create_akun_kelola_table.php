<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('akun_kelola', function (Blueprint $table) {
            $table->increments('id_akun_kelola');
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_driver')->nullable();
            $table->string('status_driver')->nullable();
            $table->text('aktivitas_akun')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('id_driver')->references('id_driver')->on('driver')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('akun_kelola');
    }
};
