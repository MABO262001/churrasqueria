<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categorie;
use App\Models\Sub_Categorie;

class SubCategorieSeeder extends Seeder
{
    public function run(): void
    {
        $carnes = Categorie::where('name', 'Carnes')->first();
        $acomp = Categorie::where('name', 'Acompañamientos')->first();
        $bebidas = Categorie::where('name', 'Bebidas')->first();
        $postres = Categorie::where('name', 'Postres')->first();

        // Subcategorías de Carnes
        $subCarnes = [
            'Res', 'Cerdo', 'Pollo', 'Pescado', 'Embutidos', 'Parrilladas'
        ];
        foreach ($subCarnes as $sub) {
            Sub_Categorie::firstOrCreate([
                'name' => $sub,
                'categories_id' => $carnes->id,
            ], ['url_photo' => null]);
        }

        // Subcategorías de Acompañamientos
        $subAcomp = [
            'Ensaladas', 'Arroces', 'Papas', 'Yuca', 'Sopas'
        ];
        foreach ($subAcomp as $sub) {
            Sub_Categorie::firstOrCreate([
                'name' => $sub,
                'categories_id' => $acomp->id,
            ], ['url_photo' => null]);
        }

        // Subcategorías de Bebidas (ampliadas)
        $subBebidas = [
            'Sodas', 'Cervezas', 'Vinos', 'Licores', 'Jugos', 'Aguas frescas'
        ];
        foreach ($subBebidas as $sub) {
            Sub_Categorie::firstOrCreate([
                'name' => $sub,
                'categories_id' => $bebidas->id,
            ], ['url_photo' => null]);
        }

        // Subcategorías de Postres
        $subPostres = [
            'Gelatina', 'Helado', 'Flan', 'Torta', 'Dulce de leche'
        ];
        foreach ($subPostres as $sub) {
            Sub_Categorie::firstOrCreate([
                'name' => $sub,
                'categories_id' => $postres->id,
            ], ['url_photo' => null]);
        }
    }
}
