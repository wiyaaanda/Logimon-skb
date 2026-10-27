<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('akun_kelola', function (Blueprint $table) {
            // Make id_user nullable since we also track driver activities
            $table->unsignedInteger('id_user')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('akun_kelola', function (Blueprint $table) {
            $table->unsignedInteger('id_user')->nullable(false)->change();
        });
    }
};
