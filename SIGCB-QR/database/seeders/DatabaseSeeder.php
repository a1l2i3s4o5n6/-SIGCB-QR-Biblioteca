<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@biblioteca.com',
            'password' => bcrypt('admin123'),
        ]);

        User::create([
            'name' => 'María López',
            'email' => 'biblio@biblioteca.com',
            'password' => bcrypt('admin123'),
        ]);

        User::create([
            'name' => 'Carlos García',
            'email' => 'estudiante@estudiante.com',
            'password' => bcrypt('123456'),
        ]);

        $this->command->info('Usuarios de prueba creados correctamente.');
    }
}
