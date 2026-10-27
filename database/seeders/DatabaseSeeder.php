<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            TransporterSeeder::class,
            DriverSeeder::class,
            KendaraanSeeder::class,
            KategoriMaterialSeeder::class,
            MaterialSeeder::class,
            AsalTujuanSeeder::class,
        ]);
    }
}
