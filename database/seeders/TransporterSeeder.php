<?php

namespace Database\Seeders;

use App\Models\Transporter;
use Illuminate\Database\Seeder;

class TransporterSeeder extends Seeder
{
    public function run()
    {
        Transporter::create(['nama_transporter' => 'SKBtrans Banjarbaru', 'no_telp_transporter' => '08123456789']);
        Transporter::create(['nama_transporter' => 'Ekspedisi Contoh', 'no_telp_transporter' => '08129876543']);
    }
}
