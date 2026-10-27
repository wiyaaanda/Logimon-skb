<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kategori_material', function (Blueprint $table) {
            $table->increments('id_kategori_material');
            $table->string('code_material')->nullable();
            $table->integer('qty_material')->nullable();
            $table->string('satuan_material')->nullable();
            $table->float('harga_material')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kategori_material');
    }
};
