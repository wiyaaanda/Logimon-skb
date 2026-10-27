<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('status_log') && Schema::hasColumn('status_log', 'kategori_kendala')) {
            DB::table('status_log')
                ->whereNull('keterangan_log')
                ->whereNotNull('kategori_kendala')
                ->update(['keterangan_log' => DB::raw('kategori_kendala')]);

            Schema::table('status_log', function (Blueprint $table) {
                $table->dropColumn('kategori_kendala');
            });
        }

        if (Schema::hasTable('statistik_pengiriman') && Schema::hasColumn('statistik_pengiriman', 'kendala_terbanyak')) {
            Schema::table('statistik_pengiriman', function (Blueprint $table) {
                $table->dropColumn('kendala_terbanyak');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('status_log') && ! Schema::hasColumn('status_log', 'kategori_kendala')) {
            Schema::table('status_log', function (Blueprint $table) {
                $table->string('kategori_kendala')->nullable();
            });
        }

        if (Schema::hasTable('statistik_pengiriman') && ! Schema::hasColumn('statistik_pengiriman', 'kendala_terbanyak')) {
            Schema::table('statistik_pengiriman', function (Blueprint $table) {
                $table->string('kendala_terbanyak')->nullable();
            });
        }
    }
};
