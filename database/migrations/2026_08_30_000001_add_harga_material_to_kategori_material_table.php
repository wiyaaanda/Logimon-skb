<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('kategori_material', 'harga_material')) {
            Schema::table('kategori_material', function (Blueprint $table) {
                $table->float('harga_material')->nullable()->after('satuan_material');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('kategori_material', 'harga_material')) {
            Schema::table('kategori_material', function (Blueprint $table) {
                $table->dropColumn('harga_material');
            });
        }
    }
};
