<?php

namespace Database\Seeders;

use App\Models\KategoriMaterial;
use Illuminate\Database\Seeder;

class KategoriMaterialSeeder extends Seeder
{
    public function run()
    {
        KategoriMaterial::create(['code_material' => 'MAT-A', 'qty_material' => 100, 'satuan_material' => 'kg', 'harga_material' => 10000]);
        KategoriMaterial::create(['code_material' => 'MAT-B', 'qty_material' => 50, 'satuan_material' => 'kg', 'harga_material' => 15000]);
    }
}
