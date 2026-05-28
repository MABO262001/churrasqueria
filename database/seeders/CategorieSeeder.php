<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        Categorie::firstOrCreate(['name' => 'Carnes']);
        Categorie::firstOrCreate(['name' => 'Acompañamientos']);
        Categorie::firstOrCreate(['name' => 'Bebidas']);
        Categorie::firstOrCreate(['name' => 'Postres']);
    }
}
