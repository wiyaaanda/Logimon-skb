<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run()
    {
        Material::create(['nama_material' => 'Batu Bara', 'jenis_material' => 'Mineral', 'stock_status_material' => 'Tersedia', 'harga_material' => 100000, 'id_kategori_material' => 1]);
        Material::create(['nama_material' => 'Semen', 'jenis_material' => 'Bangunan', 'stock_status_material' => 'Tersedia', 'harga_material' => 75000, 'id_kategori_material' => 2]);
    }
}
