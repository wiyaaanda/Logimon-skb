<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('driver', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id_driver');
            $table->string('password')->nullable()->after('username');
        });
    }

    public function down()
    {
        Schema::table('driver', function (Blueprint $table) {
            $table->dropColumn(['username', 'password']);
        });
    }
};
