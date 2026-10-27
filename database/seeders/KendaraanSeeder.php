<?php

namespace Database\Seeders;

use App\Models\Kendaraan;
use Illuminate\Database\Seeder;

class KendaraanSeeder extends Seeder
{
    public function run()
    {
        Kendaraan::create(['no_pol' => 'KT-1234-BA', 'jenis_kendaraan' => 'Truk', 'id_transporter' => 1]);
        Kendaraan::create(['no_pol' => 'KT-5678-BA', 'jenis_kendaraan' => 'Truk', 'id_transporter' => 1]);
    }
}
