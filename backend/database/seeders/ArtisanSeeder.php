<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ArtisanSeeder extends Seeder
{
    public function run(): void
    {
        $artisans = [
            ['name' => 'Albornoz Nelly', 'phone' => '2996348416', 'specialty' => 'Tejido Crochet, Tejido a Dos Agujas y Bordado a Mano'],
            ['name' => 'Atencio Carla', 'phone' => '2996255976', 'specialty' => 'Bijouteria, Pintura Decorativa y Manualidades'],
            ['name' => 'Camp Barbara', 'phone' => '2995896300', 'specialty' => 'Bordado a Mano y Textil'],
            ['name' => 'Cofre Nelly', 'phone' => '2995367997', 'specialty' => 'Ceramica, Manualidades, Bijouteria y Joyeria en Alpaca'],
            ['name' => 'Contreras Sandra', 'phone' => '2995347972', 'specialty' => 'Telar Ancestral y Fotografia de Naturaleza'],
            ['name' => 'Fernandez Elsa', 'phone' => '2995335930', 'specialty' => 'Textil, Accesorios en Tela'],
            ['name' => 'Gatas Veronica', 'phone' => '2994159349', 'specialty' => 'Joyeria Artesanal, Ceramica, Pintura Decorativa'],
            ['name' => 'Morales Florencia', 'phone' => '2995948456', 'specialty' => 'Macrame, Alambrismo'],
            ['name' => 'Ojeda Margarita', 'phone' => '2995702625', 'specialty' => 'Cuero, Textil y Madera'],
            ['name' => 'Ojeda Yanina', 'phone' => '2994026088', 'specialty' => 'Tejidos con Totora'],
            ['name' => 'Penalba Ramiro', 'phone' => '2995099832', 'specialty' => 'Madera, Cuero y Herreria'],
            ['name' => 'Peralta Silvia', 'phone' => '2994069933', 'specialty' => 'Munecos en Tela, Porcelana Fria y Manualidades'],
            ['name' => 'Quiroga Adriana', 'phone' => '2995306321', 'specialty' => 'Tejido Telar Maria y Textil'],
            ['name' => 'Rotta Brenda', 'phone' => '2994114908', 'specialty' => 'Sahumerios Artesanales'],
            ['name' => 'Sosa Daniela', 'phone' => '2994835611', 'specialty' => 'Bijouteria en Macrame, Munecos de Tela, Accesorios para Bebe, Textil y Madera'],
        ];

        foreach ($artisans as $data) {
            $email = strtolower(str_replace(' ', '.', $data['name'])) . '@artesanos.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('artesano1234'),
                    'role' => 'cliente',
                    'phone' => $data['phone'],
                ]
            );

            Artisan::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty' => $data['specialty'],
                    'bio' => null,
                    'location' => null,
                    'photo' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
