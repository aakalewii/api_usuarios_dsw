<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@tienda.com',
            'password' => Hash::make('password'),
            'rol' => Rol::ADMINISTRADOR,
        ]);

        User::create([
            'name' => 'Gestor',
            'email' => 'gestor@tienda.com',
            'password' => Hash::make('password'),
            'rol' => Rol::GESTOR,
        ]);

        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@tienda.com',
            'password' => Hash::make('password'),
            'rol' => Rol::CLIENTE,
        ]);
    }
}
