<?php

namespace Database\Seeders;

use App\Models\Asal;
use App\Models\Tujuan;
use Illuminate\Database\Seeder;

class AsalTujuanSeeder extends Seeder
{
    public function run()
    {
        Asal::create(['nama_asal' => 'Gudang A', 'kota_asal' => 'Banjarbaru', 'provensi_asal' => 'Kalimantan Selatan']);
        Tujuan::create(['nama_tujuan' => 'Pabrik X', 'kota_tujuan' => 'Banjarmasin', 'provensi_tujuan' => 'Kalimantan Selatan']);
    }
}
