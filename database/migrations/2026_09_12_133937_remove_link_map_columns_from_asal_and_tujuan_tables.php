<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asal', function (Blueprint $table) {
            $table->dropColumn('link_map_asal');
        });

        Schema::table('tujuan', function (Blueprint $table) {
            $table->dropColumn('link_map_tujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asal', function (Blueprint $table) {
            $table->string('link_map_asal')->nullable();
        });

        Schema::table('tujuan', function (Blueprint $table) {
            $table->string('link_map_tujuan')->nullable();
        });
    }
};
