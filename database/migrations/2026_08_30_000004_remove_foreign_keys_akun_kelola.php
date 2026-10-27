<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop existing foreign keys from akun_kelola
        Schema::table('akun_kelola', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropForeign(['id_driver']);
        });
    }

    public function down()
    {
        Schema::table('akun_kelola', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('id_driver')->references('id_driver')->on('driver')->onDelete('cascade');
        });
    }
};
