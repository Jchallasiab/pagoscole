<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Ejecuta los seeds de usuarios por defecto.
     */
    public function run(): void
    {
        // 🧑‍💼 Administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        // 👩‍💼 Secretaria
        User::create([
            'name' => 'Secretaria',
            'email' => 'secretaria@secretaria.com',
            'password' => Hash::make('secretaria'),
            'role' => 'secretaria',
        ]);
    }
}
