<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@sistema.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        echo "\n✓ Usuario administrador creado exitosamente.\n";
        echo "Email: admin@sistema.local\n";
        echo "Contraseña: admin123\n\n";
    }
}
