<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal_pengiriman', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('status');
        });

        // Migrate existing tingkat_urgensi values to new priority values
        DB::table('jadwal_pengiriman')->where('tingkat_urgensi', 'Mendesak')->update(['priority' => 'urgent']);
        DB::table('jadwal_pengiriman')->where('tingkat_urgensi', 'Prioritas')->update(['priority' => 'high']);
        DB::table('jadwal_pengiriman')->whereNull('priority')->update(['priority' => 'normal']);

        Schema::table('jadwal_pengiriman', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_pengiriman', 'tingkat_urgensi')) {
                $table->dropColumn('tingkat_urgensi');
            }
        });
    }

    public function down()
    {
        Schema::table('jadwal_pengiriman', function (Blueprint $table) {
            $table->enum('tingkat_urgensi', ['Mendesak', 'Prioritas'])->nullable()->after('status');
        });

        // map back
        DB::table('jadwal_pengiriman')->where('priority', 'urgent')->update(['tingkat_urgensi' => 'Mendesak']);
        DB::table('jadwal_pengiriman')->where('priority', 'high')->update(['tingkat_urgensi' => 'Prioritas']);
        DB::table('jadwal_pengiriman')->where('priority', 'normal')->update(['tingkat_urgensi' => null]);

        Schema::table('jadwal_pengiriman', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_pengiriman', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};
