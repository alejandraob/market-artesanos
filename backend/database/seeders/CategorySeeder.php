<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Textiles y Tejidos' => [
                'Crochet / Dos Agujas',
                'Telar Ancestral',
                'Bordado',
                'Accesorios Textiles',
            ],
            'Joyería y Accesorios' => [
                'Joyería en Alpaca',
                'Macramé / Alambrismo',
                'Bijouterie',
            ],
            'Hogar y Decoración' => [
                'Cerámica',
                'Pintura Decorativa',
                'Porcelana Fría',
                'Sahumerios',
            ],
            'Materiales Nobles' => [
                'Carpintería',
                'Marroquinería',
                'Herrería de Autor',
            ],
            'Arte y Juguetes' => [
                'Muñecos de Tela',
                'Fotografía de Naturaleza',
            ],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
            ]);

            foreach ($children as $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
