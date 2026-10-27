<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_operasional', function (Blueprint $table) {
            $table->string('code_material')->nullable()->after('id_material');
        });

        Schema::table('data_volume', function (Blueprint $table) {
            $table->string('code_material')->nullable()->after('id_material');
        });

        Schema::table('data_timbangan', function (Blueprint $table) {
            $table->string('code_material')->nullable()->after('id_material');
        });
    }

    public function down(): void
    {
        Schema::table('data_operasional', function (Blueprint $table) {
            $table->dropColumn('code_material');
        });

        Schema::table('data_volume', function (Blueprint $table) {
            $table->dropColumn('code_material');
        });

        Schema::table('data_timbangan', function (Blueprint $table) {
            $table->dropColumn('code_material');
        });
    }
};
