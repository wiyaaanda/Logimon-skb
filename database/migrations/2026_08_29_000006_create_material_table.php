<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('material', function (Blueprint $table) {
            $table->increments('id_material');
            $table->string('nama_material');
            $table->string('jenis_material')->nullable();
            $table->string('stock_status_material')->nullable();
            $table->float('harga_material')->nullable();
            $table->unsignedInteger('id_kategori_material')->nullable();
            $table->timestamps();

            $table->foreign('id_kategori_material')->references('id_kategori_material')->on('kategori_material')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('material');
    }
};
