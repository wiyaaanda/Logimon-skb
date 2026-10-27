<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('jadwal_pengiriman', 'no_rec')) {
            Schema::table('jadwal_pengiriman', function (Blueprint $table) {
                $table->string('no_rec')->nullable()->after('code_material');
                $table->time('jam_berangkat')->nullable()->after('no_rec');
                $table->float('vol_kg')->nullable()->after('jam_berangkat');
                $table->float('timbangan_cv_sr')->nullable()->after('vol_kg');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('jadwal_pengiriman', 'no_rec')) {
            Schema::table('jadwal_pengiriman', function (Blueprint $table) {
                $table->dropColumn(['no_rec', 'jam_berangkat', 'vol_kg', 'timbangan_cv_sr']);
            });
        }
    }
};
