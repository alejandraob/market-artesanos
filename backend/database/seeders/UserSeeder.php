<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Presidente
        $presidente = User::firstOrCreate(
            ['email' => 'admin@artesanos.com'],
            [
                'name' => 'Presidente de Artesanos',
                'password' => Hash::make('password'),
                'role' => 'presidente',
            ]
        );

        Artisan::firstOrCreate(
            ['user_id' => $presidente->id],
            [
                'bio' => 'Líder de la asociación y tejedor experto.',
                'specialty' => 'Tejido Ancestral',
                'location' => 'Catriel',
            ]
        );

        // Cliente Test
        User::firstOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name' => 'Juan Perez',
                'password' => Hash::make('password'),
                'role' => 'cliente',
            ]
        );

        // Cliente Artesanos
        User::firstOrCreate(
            ['email' => 'cliente@artesanos.com'],
            [
                'name' => 'Cliente Artesanos',
                'password' => Hash::make('password'),
                'role' => 'cliente',
            ]
        );
    }
}
