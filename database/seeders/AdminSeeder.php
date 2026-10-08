<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tescha.edu.mx'],
            [
                'name'     => 'Mtra. Niza',
                'email'    => 'admin@tescha.edu.mx',
                'password' => Hash::make('Tescha2025*'),
            ]
        );
    }
}