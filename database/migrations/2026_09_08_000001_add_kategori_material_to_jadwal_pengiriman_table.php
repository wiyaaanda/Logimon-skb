<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pengiriman', function (Blueprint $table) {
            $table->unsignedInteger('id_kategori_material')->nullable()->after('id_material');
            $table->foreign('id_kategori_material')
                ->references('id_kategori_material')
                ->on('kategori_material')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pengiriman', function (Blueprint $table) {
            $table->dropForeign(['id_kategori_material']);
            $table->dropColumn('id_kategori_material');
        });
    }
};
