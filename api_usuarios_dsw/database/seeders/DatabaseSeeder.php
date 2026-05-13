<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@habita.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('Admin1234'),
                'rol'      => Rol::ADMINISTRADOR,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@habita.com'],
            [
                'name'     => 'Cliente',
                'password' => Hash::make('Cliente1234'),
                'rol'      => Rol::CLIENTE,
            ]
        );
    }
}
