<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'driver1'],
            [
                'name' => 'Driver Satu',
                'email' => 'driver1@example.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}
