<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run()
    {
        Driver::create(['nama_driver' => 'Budi', 'no_telp_driver' => '0811000001', 'id_transporter' => 1]);
        Driver::create(['nama_driver' => 'Andi', 'no_telp_driver' => '0811000002', 'id_transporter' => 1]);
    }
}
